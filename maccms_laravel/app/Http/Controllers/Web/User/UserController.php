<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Web\BaseController;
use App\Libraries\Login\ThinkOauth;
use App\Models\Card;
use App\Models\Cash;
use App\Models\Comment;
use App\Models\Gbook;
use App\Models\Group;
use App\Models\Msg;
use App\Models\Order;
use App\Models\Plog;
use App\Models\Type;
use App\Models\Ulog;
use App\Models\User;
use App\Models\Visit;
use App\Services\LoginEvent;
use App\Utils\QRcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email as SymfonyEmail;

class UserController extends BaseController
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();
        $user->normalizeMembership();
        $user->loadMissing('group');
        return view('user.index', compact('user'));
    }

    public function regcheck(Request $request)
    {
        $type = trim((string) $request->input('t', ''));
        $value = trim((string) $request->input('str', ''));

        if ($type === 'user_name' && User::query()->where('user_name', $value)->exists()) {
            return response($value);
        }

        if ($type === 'user_email' && User::query()->where('user_email', $value)->exists()) {
            return response($value);
        }

        if ($type === 'verify' && !$this->validateDefaultVerifyCode($value)) {
            return response()->json(['code' => 1002, 'msg' => __('verify_err')]);
        }

        return response()->json(['code' => 1, 'msg' => 'ok']);
    }

    public function reg(Request $request)
    {
        if ($request->isMethod('post')) {
            return $this->registerPost($request);
        }

        if ($request->filled('uid')) {
            cookie()->queue('uid', (string) (int) $request->input('uid'), 60 * 24 * 30);
        }

        return view('user.reg', [
            'userConfig' => config('maccms.user', []),
            'param' => $request->all(),
        ]);
    }

    public function regMsg(Request $request)
    {
        return $this->sendUserMessage($request, 3);
    }

    public function findpass(Request $request)
    {
        if ($request->isMethod('post')) {
            $userName = trim((string) $request->input('user_name', ''));
            $question = trim((string) $request->input('user_question', ''));
            $answer = trim((string) $request->input('user_answer', ''));
            $password = (string) $request->input('user_pwd', '');
            $password2 = (string) $request->input('user_pwd2', '');
            $verify = trim((string) $request->input('verify', ''));

            if ($userName === '' || $question === '' || $answer === '' || $password === '' || $password2 === '' || $verify === '') {
                return response()->json(['code' => 1001, 'msg' => __('param_err')]);
            }

            if (!$this->validateDefaultVerifyCode($verify)) {
                return response()->json(['code' => 1002, 'msg' => __('verify_err')]);
            }

            if ($password !== $password2) {
                return response()->json(['code' => 1003, 'msg' => __('model/user/pass_not_same_pass2')]);
            }

            $user = User::query()
                ->where('user_name', $userName)
                ->where('user_question', $question)
                ->where('user_answer', $answer)
                ->first();

            if (!$user) {
                return response()->json(['code' => 1004, 'msg' => __('model/user/findpass_not_found')]);
            }

            $user->update(['user_pwd' => md5($password)]);

            return response()->json(['code' => 1, 'msg' => __('model/user/findpass_ok')]);
        }

        return view('user.findpass', ['param' => $request->all()]);
    }

    public function findpassMsg(Request $request)
    {
        if ($request->isMethod('post')) {
            return $this->sendUserMessage($request, 2);
        }

        return view('user.findpass_msg', ['param' => $request->all()]);
    }

    public function findpassReset(Request $request)
    {
        $to = trim((string) $request->input('user_email', $request->input('to', '')));
        $password = (string) $request->input('user_pwd', '');
        $password2 = (string) $request->input('user_pwd2', '');
        $code = trim((string) $request->input('code', ''));
        $ac = trim((string) $request->input('ac', 'email'));

        if (strlen($password) < 6) {
            return $this->respondUserFlow($request, ['code' => 2002, 'msg' => __('validate/require_pass')], 'user.findpass_msg', 'user.findpass_msg');
        }

        if ($password !== $password2) {
            return $this->respondUserFlow($request, ['code' => 2003, 'msg' => __('model/user/pass_not_same_pass2')], 'user.findpass_msg', 'user.findpass_msg');
        }

        $check = $this->checkMessageCode($code, 2, 0, $to);
        if ($check['code'] > 1) {
            return $this->respondUserFlow($request, $check, 'user.findpass_msg', 'user.findpass_msg');
        }

        if ($ac === 'email') {
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                return $this->respondUserFlow($request, ['code' => 2005, 'msg' => __('model/user/email_format_err')], 'user.findpass_msg', 'user.findpass_msg');
            }
            $user = User::query()->where('user_email', $to)->first();
            if (!$user) {
                return $this->respondUserFlow($request, ['code' => 2006, 'msg' => __('model/user/email_err')], 'user.findpass_msg', 'user.findpass_msg');
            }
        } else {
            if (!preg_match('/^1\d{10}$/', $to)) {
                return $this->respondUserFlow($request, ['code' => 2007, 'msg' => __('model/user/phone_format_err')], 'user.findpass_msg', 'user.findpass_msg');
            }
            $user = User::query()->where('user_phone', $to)->first();
            if (!$user) {
                return $this->respondUserFlow($request, ['code' => 2008, 'msg' => __('model/user/phone_err')], 'user.findpass_msg', 'user.findpass_msg');
            }
        }

        $ok = $user->update(['user_pwd' => md5($password)]);
        if (!$ok) {
            return $this->respondUserFlow($request, ['code' => 2009, 'msg' => __('model/user/pass_reset_err')], 'user.findpass_msg', 'user.findpass_msg');
        }

        return $this->respondUserFlow($request, ['code' => 1, 'msg' => __('model/user/pass_reset_ok')], 'login', 'user.findpass_msg');
    }

    public function bind(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if ($request->isMethod('post')) {
            $user = Auth::user();
            $ac = trim((string) $request->input('ac', 'email'));
            $to = trim((string) $request->input('to', ''));
            $code = trim((string) $request->input('code', ''));

            $check = $this->checkMessageCode($code, 1, (int) $user->user_id, $to);
            if ($check['code'] > 1) {
                return $this->respondUserAction($request, $check, 'user.bind');
            }

            $column = $ac === 'phone' ? 'user_phone' : 'user_email';
            User::query()->where($column, $to)->update([$column => '']);
            $user->update([$column => $to]);

            return $this->respondUserAction($request, ['code' => 1, 'msg' => __('model/user/update_bind_ok')], 'user.bind');
        }

        return view('user.bind', [
            'user' => Auth::user(),
            'param' => $request->all(),
        ]);
    }

    public function bindmsg(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['code' => 1003, 'msg' => 'Login required']);
        }

        return $this->sendUserMessage($request, 1, (int) Auth::id());
    }

    public function unbind(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if ($request->isMethod('post')) {
            $ac = trim((string) $request->input('ac', 'email'));
            $column = $ac === 'phone' ? 'user_phone' : 'user_email';
            Auth::user()->update([$column => '']);

            return $this->respondUserAction($request, ['code' => 1, 'msg' => __('model/user/update_unbind_ok')], 'user.unbind');
        }

        return view('user.unbind', [
            'user' => Auth::user(),
            'param' => $request->all(),
        ]);
    }

    public function info(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if ($request->isMethod('post')) {
            $oldPassword = (string) $request->input('user_pwd', '');
            if ($oldPassword === '') {
                return $this->respondUserAction($request, ['code' => 1001, 'msg' => __('model/user/input_old_pass')], 'user.info');
            }

            if ($user->user_pwd !== md5($oldPassword) && $user->user_pwd !== $oldPassword) {
                return $this->respondUserAction($request, ['code' => 1002, 'msg' => __('model/user/old_pass_err')], 'user.info');
            }

            $newPassword = (string) $request->input('user_pwd2', '');
            $newPassword2 = (string) $request->input('user_pwd1', '');
            if ($newPassword !== '' || $newPassword2 !== '') {
                if ($newPassword !== $newPassword2) {
                    return $this->respondUserAction($request, ['code' => 1003, 'msg' => __('model/user/pass_not_same_pass2')], 'user.info');
                }
                $user->user_pwd = md5($newPassword);
            }

            $user->user_nick_name = trim((string) $request->input('user_nick_name', $user->user_nick_name));
            $user->user_qq = trim((string) $request->input('user_qq', $user->user_qq));
            $user->user_question = trim((string) $request->input('user_question', $user->user_question));
            $user->user_answer = trim((string) $request->input('user_answer', $user->user_answer));
            $user->save();

            return $this->respondUserAction($request, ['code' => 1, 'msg' => __('save_ok')], 'user.info');
        }

        return view('user.info', compact('user'));
    }

    public function portrait(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if ($request->isMethod('post')) {
            if ((string) config('maccms.user.portrait_status', '0') === '0') {
                return $this->respondUserAction($request, ['code' => 0, 'msg' => __('index/portrait_tip1')], 'user.portrait');
            }

            $file = $request->file('file') ?: $request->file('imgdata');
            if (!$file) {
                return $this->respondUserAction($request, ['code' => 1001, 'msg' => __('index/portrait_no_upload')], 'user.portrait');
            }

            $extension = strtolower((string) $file->getClientOriginalExtension());
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                return $this->respondUserAction($request, ['code' => 1002, 'msg' => __('index/portrait_ext')], 'user.portrait');
            }

            $dir = public_path('upload/user/' . date('Ymd'));
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $filename = md5(uniqid((string) Auth::id(), true)) . '.' . $extension;
            $file->move($dir, $filename);
            $path = 'upload/user/' . date('Ymd') . '/' . $filename;

            $user = Auth::user();
            $user->user_portrait = $path;
            $user->user_portrait_thumb = $path;
            $user->save();
            $user->queueLegacyCookies();

            return $this->respondUserAction($request, [
                'code' => 1,
                'msg' => __('save_ok'),
                'data' => [
                    'path' => $path,
                    'url' => asset($path),
                ],
            ], 'user.portrait');
        }

        return view('user.portrait', ['user' => Auth::user()]);
    }

    public function buy(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if ($request->isMethod('post')) {
            $flag = (string) $request->input('flag', '');
            if ($flag === 'card') {
                return $this->useCardRecharge($user, $request);
            }

            $price = (float) $request->input('price', 0);
            if ($price <= 0) {
                return $this->respondUserAction($request, ['code' => 1001, 'msg' => __('param_err')], 'user.buy');
            }

            $min = (float) config('maccms.pay.min', 1);
            if ($price < $min) {
                return $this->respondUserAction($request, ['code' => 1002, 'msg' => __('index/min_pay', [$min])], 'user.buy');
            }

            $order = Order::query()->create([
                'user_id' => $user->user_id,
                'order_code' => 'PAY' . (function_exists('mac_get_uniqid_code') ? mac_get_uniqid_code() : uniqid()),
                'order_price' => $price,
                'order_time' => time(),
                'order_points' => (int) ((float) config('maccms.pay.scale', 1) * $price),
                'order_status' => 0,
                'order_pay_type' => '',
                'order_pay_time' => 0,
                'order_remarks' => '',
            ]);

            return $this->respondUserAction($request, [
                'code' => 1,
                'msg' => __('save_ok'),
                'data' => $order,
            ], 'user.pay', ['order_code' => $order->order_code]);
        }

        return view('user.buy', [
            'user' => $user,
            'config' => config('maccms.pay', []),
        ]);
    }

    public function pay(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $orderCode = trim((string) $request->input('order_code', ''));
        $order = Order::query()
            ->where('order_code', $orderCode)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) {
            return redirect()->route('user.orders')->withErrors(['msg' => __('index/order_not')]);
        }

        return view('user.pay', [
            'order' => $order,
            'config' => config('maccms.pay', []),
            'paymentMethods' => $this->getEnabledPaymentMethods(),
        ]);
    }

    public function gopay(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $orderId = (int) $request->input('order_id', 0);
        $orderCode = trim((string) $request->input('order_code', ''));
        $payment = strtolower(trim((string) $request->input('payment', '')));
        $order = Order::query()
            ->where('user_id', Auth::id())
            ->where(function ($query) use ($orderId, $orderCode) {
                if ($orderId > 0) {
                    $query->orWhere('order_id', $orderId);
                }
                if ($orderCode !== '') {
                    $query->orWhere('order_code', $orderCode);
                }
            })
            ->first();

        if (!$order) {
            return redirect()->route('user.orders')->withErrors(['msg' => __('index/order_not')]);
        }

        if ((int) $order->order_status === 1) {
            return redirect()->route('user.pay', ['order_code' => $order->order_code])->withErrors(['msg' => __('index/order_payed')]);
        }

        $paymentMethods = $this->getEnabledPaymentMethods();
        if ($payment === '' || !isset($paymentMethods[$payment])) {
            return redirect()->route('user.pay', ['order_code' => $order->order_code])->withErrors(['msg' => __('index/payment_status')]);
        }

        $class = $this->getPaymentClass($payment);
        if ($class === null || !class_exists($class)) {
            return redirect()->route('user.pay', ['order_code' => $order->order_code])->withErrors(['msg' => '支付接口不存在']);
        }

        $this->hydrateLegacyPayGlobals($request);
        $handler = app($class);
        $user = Auth::user();

        try {
            if ($payment === 'weixin') {
                $paymentData = $handler->submit($user->toArray(), $order->toArray(), $request->all());
                if (!is_array($paymentData) || empty($paymentData['code_url'])) {
                    return redirect()->route('user.pay', ['order_code' => $order->order_code])->withErrors(['msg' => '微信支付二维码获取失败']);
                }

                return view('user.payment_weixin', [
                    'order' => $order,
                    'payment' => $payment,
                    'paymentLabel' => $paymentMethods[$payment],
                    'paymentData' => $paymentData,
                ]);
            }

            if ($payment === 'alipay') {
                return response($this->buildAlipaySubmitHtml($handler, $user->toArray(), $order->toArray()));
            }

            $paymentUrl = match ($payment) {
                'codepay' => $this->buildCodepayUrl($order->toArray(), $request->all()),
                'zhapay' => $this->buildZhapayUrl($order->toArray(), $request->all()),
                default => null,
            };

            if ($paymentUrl !== null) {
                return redirect()->away($paymentUrl);
            }
        } catch (\Throwable $e) {
            return redirect()->route('user.pay', ['order_code' => $order->order_code])->withErrors(['msg' => $e->getMessage()]);
        }

        return view('user.gopay', [
            'order' => $order,
            'payment' => $payment,
            'paymentLabel' => $paymentMethods[$payment] ?? $payment,
            'config' => config('maccms.pay', []),
        ]);
    }

    public function upgrade(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if ($request->isMethod('post')) {
            $groupId = (int) $request->input('group_id', 0);
            $long = (string) $request->input('long', '');
            $pointsLong = ['day' => 86400, 'week' => 86400 * 7, 'month' => 86400 * 30, 'year' => 86400 * 365];

            if (!isset($pointsLong[$long])) {
                return $this->respondUserAction($request, ['code' => 1001, 'msg' => '非法操作'], 'user.upgrade');
            }
            if ($groupId < 3) {
                return $this->respondUserAction($request, ['code' => 1002, 'msg' => __('model/user/select_diy_group_err')], 'user.upgrade');
            }

            $group = Group::query()->find($groupId);
            if (!$group) {
                return $this->respondUserAction($request, ['code' => 1003, 'msg' => __('model/user/group_not_found')], 'user.upgrade');
            }
            if ((int) $group->group_status === 0) {
                return $this->respondUserAction($request, ['code' => 1004, 'msg' => __('model/user/group_is_close')], 'user.upgrade');
            }

            $point = (int) ($group->{'group_points_' . $long} ?? 0);
            if ((int) $user->user_points < $point) {
                return $this->respondUserAction($request, ['code' => 1005, 'msg' => __('model/user/potins_not_enough')], 'user.upgrade');
            }

            $endTime = max(time(), (int) $user->user_end_time) + $pointsLong[$long];
            $ok = $user->forceFill([
                'user_points' => (int) $user->user_points - $point,
                'user_end_time' => $endTime,
                'group_id' => (string) $groupId,
            ])->save();

            if (!$ok) {
                return $this->respondUserAction($request, ['code' => 1009, 'msg' => __('model/user/update_group_err')], 'user.upgrade');
            }

            Plog::query()->create([
                'user_id' => $user->user_id,
                'plog_type' => 7,
                'plog_points' => $point,
                'plog_time' => time(),
                'plog_remarks' => __('model/user/update_group_ok'),
            ]);

            $user->reward($point);

            return $this->respondUserAction($request, ['code' => 1, 'msg' => __('model/user/update_group_ok')], 'user.upgrade');
        }

        $groups = Group::query()->where('group_id', '>', 2)->orderBy('group_id')->get();

        return view('user.upgrade', [
            'user' => $user,
            'groups' => $groups,
        ]);
    }

    public function plays(Request $request)
    {
        return $this->renderUlogList($request, 4, '播放记录');
    }

    public function downs(Request $request)
    {
        return $this->renderUlogList($request, 5, '下载记录');
    }

    public function favs(Request $request)
    {
        return $this->renderUlogList($request, 2, '收藏记录');
    }

    public function plog(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $logs = Plog::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('plog_id')
            ->paginate(20);

        return view('user.plog', ['logs' => $logs]);
    }

    public function reward(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $level = (string) $request->input('level', '1');
        $column = match ($level) {
            '2' => 'user_pid_2',
            '3' => 'user_pid_3',
            default => 'user_pid',
        };

        $users = User::query()
            ->where($column, Auth::id())
            ->orderByDesc('user_id')
            ->paginate(20);

        return view('user.reward', [
            'users' => $users,
            'level' => $level,
        ]);
    }

    public function orders(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $orders = Order::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('order_id')
            ->paginate(20);

        return view('user.orders', ['orders' => $orders]);
    }

    public function orderInfo(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $order = Order::query()
            ->where('order_id', (int) $request->input('order_id', 0))
            ->where('user_id', Auth::id())
            ->first();

        if ($request->ajax()) {
            if (!$order) {
                return response()->json(['code' => 1002, 'msg' => __('index/order_not')]);
            }

            return response()->json(['code' => 1, 'msg' => 'ok', 'info' => $order]);
        }

        return view('user.order_info', ['order' => $order]);
    }

    public function cards(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $cards = Card::query()
            ->where('user_id', Auth::id())
            ->where('card_use_status', 1)
            ->orderByDesc('card_id')
            ->paginate(20);

        return view('user.cards', ['cards' => $cards]);
    }

    public function qrcode(Request $request)
    {
        $data = (string) $request->input('data', '');
        if (!str_starts_with($data, 'weixin')) {
            abort(400, __('param_err'));
        }

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        ob_start();
        QRcode::png($data, false, QR_ECLEVEL_L, 10);
        $content = ob_get_clean();

        return response($content, 200)->header('Content-Type', 'image/png');
    }

    public function cash(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if ($request->isMethod('post')) {
            if ((string) config('maccms.user.cash_status', '0') !== '1') {
                return $this->respondUserAction($request, ['code' => 1005, 'msg' => __('model/cash/not_open')], 'user.cash');
            }

            $money = (float) $request->input('cash_money', 0);
            $min = (float) config('maccms.user.cash_min', 0);
            if ($money < $min) {
                return $this->respondUserAction($request, ['code' => 1006, 'msg' => __('model/cash/min_money_err') . '：' . $min], 'user.cash');
            }

            $points = (int) round($money * (float) config('maccms.user.cash_ratio', 1));
            if ($points > (int) $user->user_points) {
                return $this->respondUserAction($request, ['code' => 1007, 'msg' => __('model/cash/mush_money_err')], 'user.cash');
            }

            Cash::query()->create([
                'user_id' => $user->user_id,
                'cash_status' => 0,
                'cash_points' => $points,
                'cash_money' => $money,
                'cash_bank_name' => trim((string) $request->input('cash_bank_name', '')),
                'cash_bank_no' => trim((string) $request->input('cash_bank_no', '')),
                'cash_payee_name' => trim((string) $request->input('cash_payee_name', '')),
                'cash_time' => time(),
                'cash_time_audit' => 0,
            ]);

            $user->forceFill([
                'user_points' => (int) $user->user_points - $points,
                'user_points_froze' => (int) $user->user_points_froze + $points,
            ])->save();

            return $this->respondUserAction($request, ['code' => 1, 'msg' => __('save_ok')], 'user.cash');
        }

        $records = Cash::query()
            ->where('user_id', $user->user_id)
            ->orderByDesc('cash_id')
            ->paginate(20);

        return view('user.cash', [
            'user' => $user,
            'records' => $records,
            'config' => config('maccms.user', []),
        ]);
    }

    public function ulogDel(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['code' => 1003, 'msg' => __('index/no_login')], 403);
        }

        $type = (string) $request->input('type', '');
        $all = (string) $request->input('all', '');
        $ids = $this->normalizeIds((string) $request->input('ids', ''));
        if (!in_array($type, ['1', '2', '3', '4', '5'], true) || ($ids === [] && $all !== '1')) {
            return response()->json(['code' => 1001, 'msg' => __('param_err')]);
        }

        $query = Ulog::query()
            ->where('user_id', Auth::id())
            ->where('ulog_type', (int) $type);
        if ($all !== '1') {
            $query->whereIn('ulog_id', $ids);
        }

        $query->delete();

        return response()->json(['code' => 1, 'msg' => __('del_ok')]);
    }

    public function plogDel(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['code' => 1003, 'msg' => __('index/no_login')], 403);
        }

        $all = (string) $request->input('all', '');
        $ids = $this->normalizeIds((string) $request->input('ids', ''));
        if ($ids === [] && $all !== '1') {
            return response()->json(['code' => 1001, 'msg' => __('param_err')]);
        }

        $query = Plog::query()->where('user_id', Auth::id());
        if ($all !== '1') {
            $query->whereIn('plog_id', $ids);
        }

        $query->delete();

        return response()->json(['code' => 1, 'msg' => __('del_ok')]);
    }

    public function cashDel(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['code' => 1003, 'msg' => __('index/no_login')], 403);
        }

        /** @var User $user */
        $user = Auth::user();
        $all = (string) $request->input('all', '');
        $ids = $this->normalizeIds((string) $request->input('ids', ''));
        if ($ids === [] && $all !== '1') {
            return response()->json(['code' => 1001, 'msg' => __('param_err')]);
        }

        $query = Cash::query()->where('user_id', $user->user_id);
        if ($all !== '1') {
            $query->whereIn('cash_id', $ids);
        }

        $records = $query->get();
        foreach ($records as $record) {
            if ((int) $record->cash_status === 0) {
                $user->forceFill([
                    'user_points' => (int) $user->user_points + (int) $record->cash_points,
                    'user_points_froze' => max(0, (int) $user->user_points_froze - (int) $record->cash_points),
                ])->save();
                $user->refresh();
            }

            $record->delete();
        }

        return response()->json(['code' => 1, 'msg' => __('del_ok')]);
    }

    public function comment(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $comments = Comment::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('comment_id')
            ->paginate(20);

        return view('user.comment', ['comments' => $comments]);
    }

    public function gbook(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $gbooks = Gbook::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('gbook_id')
            ->paginate(20);

        return view('user.gbook', ['gbooks' => $gbooks]);
    }

    public function popedom(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $types = Type::query()
            ->orderBy('type_pid')
            ->orderBy('type_sort')
            ->orderBy('type_id')
            ->get()
            ->groupBy('type_pid');

        $labels = [
            1 => __('index/page_type'),
            2 => __('index/page_detail'),
            3 => __('index/page_play'),
            4 => __('index/page_down'),
            5 => __('index/try_see'),
        ];

        $tree = collect($types->get(0, collect()))->map(function ($type) use ($types, $labels) {
            return [
                'type' => $type,
                'popedom' => $this->buildPopedomMap($type, $labels),
                'children' => collect($types->get($type->type_id, collect()))->map(function ($child) use ($labels) {
                    return [
                        'type' => $child,
                        'popedom' => $this->buildPopedomMap($child, $labels),
                    ];
                }),
            ];
        });

        return view('user.popedom', ['tree' => $tree]);
    }

    public function visit(Request $request)
    {
        $uid = abs((int) $request->input('uid', 0));
        if ($uid < 1) {
            return redirect('/');
        }

        $ip = function_exists('mac_get_ip_long') ? (int) mac_get_ip_long() : ip2long($request->ip());
        $maxCount = max(1, (int) config('maccms.user.invite_visit_num', 1));
        $today = strtotime('today');

        $count = Visit::query()
            ->where('user_id', $uid)
            ->where('visit_ip', $ip)
            ->where('visit_time', '>', $today)
            ->count();

        if ($count < $maxCount) {
            $saved = Visit::query()->create([
                'user_id' => $uid,
                'visit_ip' => $ip,
                'visit_time' => time(),
                'visit_ly' => function_exists('mac_get_refer') ? (string) mac_get_refer() : '',
            ]);

            if ($saved) {
                $points = (int) config('maccms.user.invite_visit_points', 0);
                User::query()->where('user_id', $uid)->increment('user_points', $points);
                Plog::query()->create([
                    'user_id' => $uid,
                    'plog_type' => 3,
                    'plog_points' => $points,
                    'plog_time' => time(),
                    'plog_remarks' => __('model/user/visit_ok'),
                ]);
            }
        }

        $url = '/';
        if ($request->filled('url')) {
            $target = (string) $request->input('url');
            $parts = @parse_url($target);
            if (($parts['host'] ?? null) === $request->getHost()) {
                $url = $target;
            }
        }

        return redirect($url);
    }

    public function oauth(Request $request, string $type = '')
    {
        $type = $this->normalizeOauthType($type ?: (string) $request->input('type', ''));
        if ($type === null) {
            return redirect()->route('login')->withErrors(['msg' => __('param_err')]);
        }

        $connect = (array) config('maccms.connect.' . $type, []);
        if ((string) ($connect['status'] ?? '0') !== '1') {
            return redirect()->route('login')->withErrors(['msg' => 'Third-party login is disabled']);
        }

        try {
            $sns = ThinkOauth::getInstance($type);
            return redirect()->away($sns->getRequestCodeURL());
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors(['msg' => $e->getMessage()]);
        }
    }

    public function logincallback(Request $request, string $type = '', string $code = '')
    {
        $type = $this->normalizeOauthType($type ?: (string) $request->input('type', ''));
        $code = $code !== '' ? $code : (string) $request->input('code', '');
        if ($type === null || $code === '') {
            return redirect()->route('login')->withErrors(['msg' => __('param_err')]);
        }

        try {
            $sns = ThinkOauth::getInstance($type);
            $token = $sns->getAccessToken($code);
            if (!is_array($token)) {
                return redirect()->route('login')->withErrors(['msg' => __('index/logincallback2')]);
            }

            $loginEvent = app(LoginEvent::class);
            $result = $loginEvent->{$type}($token);
            if (($result['code'] ?? 0) !== 1) {
                return redirect()->route('login')->withErrors(['msg' => $result['msg'] ?? __('index/logincallback2')]);
            }

            $openid = (string) ($result['info']['openid'] ?? '');
            $col = 'user_openid_' . $type;
            if ($openid === '' || !in_array($col, ['user_openid_qq', 'user_openid_weixin'], true)) {
                return redirect()->route('login')->withErrors(['msg' => __('index/logincallback2')]);
            }

            if (Auth::check()) {
                /** @var User $currentUser */
                $currentUser = Auth::user();
                if ((string) $currentUser->{$col} === $openid) {
                    return redirect()->route('user.index')->with('success', __('index/bind_haved'));
                }

                User::query()->where($col, $openid)->update([$col => '']);
                $currentUser->update([$col => $openid]);

                return redirect()->route('user.index')->with('success', __('index/bind_ok'));
            }

            $user = User::query()->where($col, $openid)->first();
            if (!$user) {
                $user = $this->createOauthUser($col, $openid, $result['info']);
                if (!$user) {
                    return redirect()->route('login')->withErrors(['msg' => __('index/logincallback1')]);
                }
            }

            if ((int) $user->user_status === 0) {
                return redirect()->route('login')->withErrors(['msg' => 'Account is disabled']);
            }

            Auth::login($user);
            $this->touchLoginMeta($user, $request);

            return redirect()->route('user.index');
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors(['msg' => __('index/logincallback2')]);
        }
    }

    protected function registerPost(Request $request)
    {
        $userConfig = config('maccms.user', []);
        if ((string) ($userConfig['status'] ?? '0') === '0' || (string) ($userConfig['reg_open'] ?? '0') === '0') {
            return $this->respondUserFlow($request, ['code' => 1001, 'msg' => __('model/user/not_open_reg')], 'user.index', 'user.reg');
        }

        $userName = trim((string) $request->input('user_name', ''));
        $password = (string) $request->input('user_pwd', '');
        $password2 = (string) $request->input('user_pwd2', '');
        $verify = trim((string) $request->input('verify', ''));
        $uid = (int) ($request->input('uid', $request->cookie('uid', 0)));

        if ($userName === '' || $password === '' || $password2 === '') {
            return $this->respondUserFlow($request, ['code' => 1002, 'msg' => __('model/user/input_require')], 'user.index', 'user.reg');
        }

        if ((string) ($userConfig['reg_verify'] ?? '0') === '1' && !$this->validateDefaultVerifyCode($verify)) {
            return $this->respondUserFlow($request, ['code' => 1003, 'msg' => __('verify_err')], 'user.index', 'user.reg');
        }

        if ($password !== $password2) {
            return $this->respondUserFlow($request, ['code' => 1004, 'msg' => __('model/user/pass_not_pass2')], 'user.index', 'user.reg');
        }

        if (User::query()->where('user_name', $userName)->exists()) {
            return $this->respondUserFlow($request, ['code' => 1005, 'msg' => __('model/user/haved_reg')], 'user.index', 'user.reg');
        }

        if (!preg_match('/^[a-zA-Z\d]*$/', $userName)) {
            return $this->respondUserFlow($request, ['code' => 1006, 'msg' => __('model/user/name_contain')], 'user.index', 'user.reg');
        }

        if (strlen($userName) < 6) {
            return $this->respondUserFlow($request, ['code' => 1007, 'msg' => __('validate/require_name_min')], 'user.index', 'user.reg');
        }

        $filterWords = trim((string) ($userConfig['filter_words'] ?? ''));
        if ($filterWords !== '') {
            $filterArr = array_filter(array_map('trim', explode(',', $filterWords)));
            if (str_replace($filterArr, '', $userName) !== $userName) {
                return $this->respondUserFlow($request, ['code' => 1008, 'msg' => __('model/user/name_filter', [$filterWords])], 'user.index', 'user.reg');
            }
        }

        $ip = function_exists('mac_get_ip_long') ? (int) mac_get_ip_long() : ip2long($request->ip());
        $regNum = (int) ($userConfig['reg_num'] ?? 0);
        if ($regNum > 0) {
            $todayCount = User::query()
                ->where('user_reg_ip', $ip)
                ->where('user_reg_time', '>', strtotime('today'))
                ->count();
            if ($todayCount >= $regNum) {
                return $this->respondUserFlow($request, ['code' => 1009, 'msg' => __('model/user/ip_limit', [$regNum])], 'user.index', 'user.reg');
            }
        }

        $fields = [
            'user_name' => $userName,
            'user_pwd' => md5($password),
            'group_id' => '2',
            'user_points' => (int) ($userConfig['reg_points'] ?? 0),
            'user_status' => (int) ($userConfig['reg_status'] ?? 0),
            'user_reg_time' => time(),
            'user_reg_ip' => $ip,
            'user_random' => md5((string) random_int(10000000, 99999999)),
        ];

        if ((string) ($userConfig['reg_phone_sms'] ?? '0') === '1') {
            $to = trim((string) $request->input('to', ''));
            $code = trim((string) $request->input('code', ''));
            $check = $this->checkMessageCode($code, 3, 0, $to);
            if ($check['code'] > 1) {
                return $this->respondUserFlow($request, $check, 'user.index', 'user.reg');
            }
            if (User::query()->where('user_phone', $to)->exists()) {
                return $this->respondUserFlow($request, ['code' => 1011, 'msg' => __('model/user/phone_haved')], 'user.index', 'user.reg');
            }
            $fields['user_phone'] = $to;
        } elseif ((string) ($userConfig['reg_email_sms'] ?? '0') === '1') {
            $to = trim((string) $request->input('to', ''));
            $code = trim((string) $request->input('code', ''));
            $check = $this->checkMessageCode($code, 3, 0, $to);
            if ($check['code'] > 1) {
                return $this->respondUserFlow($request, $check, 'user.index', 'user.reg');
            }
            if (User::query()->where('user_email', $to)->exists()) {
                return $this->respondUserFlow($request, ['code' => 1012, 'msg' => __('model/user/email_haved')], 'user.index', 'user.reg');
            }
            $fields['user_email'] = $to;
        }

        $user = User::query()->create($fields);
        if (!$user) {
            return $this->respondUserFlow($request, ['code' => 1010, 'msg' => __('model/user/reg_err')], 'user.index', 'user.reg');
        }

        if ($uid > 0) {
            $invite = User::query()->find($uid);
            if ($invite) {
                $user->update([
                    'user_pid' => $invite->user_id,
                    'user_pid_2' => (int) $invite->user_pid,
                    'user_pid_3' => (int) $invite->user_pid_2,
                ]);

                $invitePoints = (int) ($userConfig['invite_reg_points'] ?? 0);
                if ($invitePoints > 0) {
                    $invite->increment('user_points', $invitePoints);
                    Plog::query()->create([
                        'user_id' => $invite->user_id,
                        'plog_type' => 2,
                        'plog_points' => $invitePoints,
                        'plog_time' => time(),
                        'plog_remarks' => __('model/user/reg_ok'),
                    ]);
                }
            }
        }

        Auth::login($user);
        $this->touchLoginMeta($user, $request);

        return $this->respondUserFlow($request, ['code' => 1, 'msg' => __('model/user/reg_ok') . '，Login successful'], 'user.index', 'user.reg');
    }

    protected function sendUserMessage(Request $request, int $type, ?int $userId = null)
    {
        $ac = trim((string) $request->input('ac', 'email'));
        $to = trim((string) $request->input('to', ''));
        if (!in_array($ac, ['email', 'phone'], true) || $to === '') {
            return response()->json(['code' => 9001, 'msg' => __('param_err')]);
        }

        $typeMap = [
            1 => ['flag' => 'bind', 'des' => 'bind'],
            2 => ['flag' => 'findpass', 'des' => 'findpass'],
            3 => ['flag' => 'reg', 'des' => 'register'],
        ];
        $typeInfo = $typeMap[$type] ?? ['flag' => 'verify', 'des' => 'verify'];

        $userId = $userId ?? (Auth::check() ? (int) Auth::id() : 0);
        if ($ac === 'email') {
            $hostCheck = $this->validateEmailHost($to, $type);
            if ($hostCheck['code'] > 1) {
                return response()->json($hostCheck);
            }
        }

        $minutes = $this->getMessageExpireMinutes($ac);
        $stime = time() - ($minutes * 60);

        $exists = Msg::query()
            ->where('user_id', $userId)
            ->where('msg_type', $type)
            ->where('msg_to', $to)
            ->where('msg_time', '>', $stime)
            ->exists();

        if ($exists) {
            return response()->json(['code' => 9002, 'msg' => __('model/user/do_not_send_frequently')]);
        }

        $code = (string) random_int(100000, 999999);
        if ($ac === 'email') {
            $result = $this->sendVerificationEmail($to, $typeInfo['flag'], $code, $minutes);
        } else {
            $result = $this->sendVerificationSms($to, $code, $typeInfo['flag'], $typeInfo['des']);
        }

        if ((int) ($result['code'] ?? 0) !== 1) {
            return response()->json([
                'code' => 9009,
                'msg' => __('model/user/msg_send_err') . '：' . (string) ($result['msg'] ?? 'unknown error'),
            ]);
        }

        $content = $ac === 'email'
            ? (string) ($result['content'] ?? 'mail')
            : (string) ($result['content'] ?? '');

        Msg::query()->create([
            'user_id' => $userId,
            'msg_type' => $type,
            'msg_status' => 0,
            'msg_to' => $to,
            'msg_code' => $code,
            'msg_content' => $content,
            'msg_time' => time(),
        ]);

        return response()->json(['code' => 1, 'msg' => __('model/user/msg_send_ok')]);
    }

    protected function checkMessageCode(string $code, int $type, int $userId = 0, string $to = ''): array
    {
        if ($code === '') {
            return ['code' => 9001, 'msg' => __('param_err')];
        }

        $minutes = $this->getMessageExpireMinutes($to !== '' && str_contains($to, '@') ? 'email' : 'phone');
        $stime = time() - ($minutes * 60);

        $query = Msg::query()
            ->where('user_id', $userId)
            ->where('msg_type', $type)
            ->where('msg_code', $code)
            ->where('msg_time', '>', $stime);

        if ($to !== '') {
            $query->where('msg_to', $to);
        }

        if (!$query->exists()) {
            return ['code' => 9002, 'msg' => __('model/user/msg_not_found')];
        }

        return ['code' => 1, 'msg' => 'ok'];
    }

    protected function getMessageExpireMinutes(string $ac): int
    {
        if ($ac === 'email') {
            return max(1, (int) config('maccms.email.time', 5));
        }

        return 5;
    }

    protected function validateEmailHost(string $email, int $type): array
    {
        if (!in_array($type, [1, 3], true)) {
            return ['code' => 1, 'msg' => 'ok'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['code' => 1001, 'msg' => __('model/user/email_format_err')];
        }

        [, $host] = explode('@', strtolower($email), 2);
        $whiteHosts = $this->parseHostList((string) config('maccms.user.email_white_hosts', ''));
        if ($whiteHosts !== [] && !isset($whiteHosts[$host])) {
            return ['code' => 1001, 'msg' => __('model/user/email_host_not_allowed')];
        }

        $blackHosts = $this->parseHostList((string) config('maccms.user.email_black_hosts', ''));
        if (isset($blackHosts[$host])) {
            return ['code' => 1002, 'msg' => __('model/user/email_host_not_allowed')];
        }

        return ['code' => 1, 'msg' => 'ok'];
    }

    protected function parseHostList(string $value): array
    {
        $hosts = preg_split('/[\s,]+/', str_replace("\r", '', $value)) ?: [];
        $result = [];
        foreach ($hosts as $host) {
            $host = strtolower(trim($host));
            if ($host !== '') {
                $result[$host] = true;
            }
        }

        return $result;
    }

    protected function sendVerificationEmail(string $to, string $flag, string $code, int $minutes): array
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['code' => 999, 'msg' => __('email_format_err')];
        }

        $config = (array) config('maccms.email', []);
        $type = strtolower(trim((string) ($config['type'] ?? '')));
        if ($type === '') {
            return ['code' => 9005, 'msg' => __('email_not_config')];
        }

        $smtp = (array) ($config['phpmailer'] ?? []);
        $host = trim((string) ($smtp['host'] ?? ''));
        $port = (int) ($smtp['port'] ?? 0);
        $username = trim((string) ($smtp['username'] ?? ''));
        $password = trim((string) ($smtp['password'] ?? ''));
        $secure = trim((string) ($smtp['secure'] ?? ''));
        $nick = trim((string) ($config['nick'] ?? ''));

        if ($host === '' || $port <= 0 || $username === '') {
            return ['code' => 1001, 'msg' => 'smtp config incomplete'];
        }

        $tpl = (array) ($config['tpl'] ?? []);
        $siteName = (string) config('maccms.site.site_name', config('app.name', 'Maccms'));
        $title = $this->renderMessageTemplate((string) ($tpl['user_' . $flag . '_title'] ?? 'Maccms Verify Code'), $siteName, $code, $minutes);
        $body = $this->renderMessageTemplate((string) ($tpl['user_' . $flag . '_body'] ?? 'Your verify code is: {$code}'), $siteName, $code, $minutes);

        try {
            $transport = new EsmtpTransport($host, $port, $secure !== '' ? $secure : null);
            if ($username !== '') {
                $transport->setUsername($username);
            }
            if ($password !== '') {
                $transport->setPassword($password);
            }

            $mailer = new Mailer($transport);
            $email = (new SymfonyEmail())
                ->from($nick !== '' ? sprintf('%s <%s>', $nick, $username) : $username)
                ->to($to)
                ->subject($title)
                ->html($body);

            $mailer->send($email);

            return ['code' => 1, 'msg' => 'ok', 'content' => $body];
        } catch (\Throwable $e) {
            return ['code' => 102, 'msg' => $e->getMessage()];
        }
    }

    protected function sendVerificationSms(string $to, string $code, string $typeFlag, string $typeDes): array
    {
        if (!preg_match('/^1[3-9]\d{9}$/', $to)) {
            return ['code' => 999, 'msg' => __('phone_format_err')];
        }

        $config = (array) config('maccms.sms', []);
        $driver = trim((string) ($config['type'] ?? ''));
        if ($driver === '') {
            return ['code' => 9005, 'msg' => __('sms_not_config')];
        }

        $content = str_replace(
            ['[用户]', '[类型]', '[时长]', '[验证码]'],
            [Auth::check() ? (string) Auth::user()->user_name : '', $typeDes, '5', $code],
            (string) ($config['content'] ?? '验证码：[验证码]，5分钟内有效')
        );

        $class = 'App\\Libraries\\Sms\\' . Str::studly(strtolower($driver));
        if (!class_exists($class)) {
            return ['code' => 991, 'msg' => __('sms_not')];
        }

        $GLOBALS['config'] = config('maccms');
        $GLOBALS['config']['sms'] = $config;

        try {
            $sender = app($class);
            $result = $sender->submit($to, $code, $typeFlag, $typeDes, $content);
            if (!is_array($result)) {
                return ['code' => 102, 'msg' => 'invalid sms response'];
            }
            $result['content'] = $content;

            return $result;
        } catch (\Throwable $e) {
            return ['code' => 102, 'msg' => $e->getMessage()];
        }
    }

    protected function renderMessageTemplate(string $template, string $siteName, string $code, int $minutes): string
    {
        return htmlspecialchars_decode(strtr($template, [
            '{$maccms.site_name}' => $siteName,
            '{$code}' => $code,
            '{$time}' => (string) $minutes,
        ]));
    }

    protected function validateDefaultVerifyCode(string $verify): bool
    {
        $saved = (string) Session::get('verify_code_default', '');
        return $verify !== '' && strcasecmp($saved, $verify) === 0;
    }

    protected function touchLoginMeta(User $user, Request $request): void
    {
        $user->refreshLoginMeta($request->ip());
        $user->refresh()->queueLegacyCookies();
    }

    protected function renderUlogList(Request $request, int $type, string $title)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $logs = Ulog::query()
            ->where('user_id', Auth::id())
            ->where('ulog_type', $type)
            ->orderByDesc('ulog_time')
            ->paginate(20);

        return view('user.ulog_records', [
            'logs' => $logs,
            'title' => $title,
        ]);
    }

    protected function useCardRecharge(User $user, Request $request)
    {
        $cardNo = trim((string) $request->input('card_no', ''));
        $cardPwd = trim((string) $request->input('card_pwd', ''));
        if ($cardNo === '' || $cardPwd === '') {
            return $this->respondUserAction($request, ['code' => 1001, 'msg' => __('param_err')], 'user.buy');
        }

        $card = Card::query()
            ->where('card_no', $cardNo)
            ->where('card_pwd', $cardPwd)
            ->where('card_use_status', 0)
            ->first();

        if (!$card) {
            return $this->respondUserAction($request, ['code' => 1002, 'msg' => __('model/card/not_found')], 'user.buy');
        }

        $user->increment('user_points', (int) $card->card_points);
        Plog::query()->create([
            'user_id' => $user->user_id,
            'plog_type' => 1,
            'plog_points' => (int) $card->card_points,
            'plog_time' => time(),
            'plog_remarks' => __('model/card/used_card_ok', [$card->card_points]),
        ]);

        $card->update([
            'card_sale_status' => 1,
            'card_use_status' => 1,
            'card_use_time' => time(),
            'user_id' => $user->user_id,
        ]);

        return $this->respondUserAction($request, ['code' => 1, 'msg' => __('model/card/used_card_ok', [$card->card_points])], 'user.buy');
    }

    protected function buildPopedomMap(Type $type, array $labels): array
    {
        $result = [];
        foreach ($labels as $key => $label) {
            if ((int) $type->type_mid !== 1 && $key > 2) {
                break;
            }
            $result[$label] = $this->hasPopedom($type->type_id, $key);
        }

        return $result;
    }

    protected function hasPopedom(int $typeId, int $popedom): bool
    {
        $user = Auth::user();
        $groupIds = array_filter(array_map('intval', explode(',', (string) $user->group_id)));
        if ($groupIds === []) {
            return false;
        }

        $groups = Group::query()->whereIn('group_id', $groupIds)->get()->keyBy('group_id');
        foreach ($groupIds as $groupId) {
            $group = $groups->get($groupId);
            if (!$group) {
                continue;
            }

            $typeList = array_filter(array_map('intval', explode(',', (string) $group->group_type)));
            if (!in_array($typeId, $typeList, true)) {
                continue;
            }

            $popedomMap = (array) ($group->group_popedom ?? []);
            if (!empty($popedomMap[$typeId][$popedom])) {
                return true;
            }
        }

        return false;
    }

    protected function normalizeOauthType(string $type): ?string
    {
        $type = strtolower(trim($type));
        return in_array($type, ['qq', 'weixin'], true) ? $type : null;
    }

    protected function createOauthUser(string $column, string $openid, array $oauthInfo): ?User
    {
        $baseName = Str::limit(preg_replace('/[^A-Za-z0-9]/', '', $openid), 10, '');
        $baseName = $baseName !== '' ? $baseName : 'oauthuser';
        $userName = $baseName;
        $suffix = 1;
        while (User::query()->where('user_name', $userName)->exists()) {
            $userName = Str::limit($baseName, 6, '') . $suffix;
            $suffix++;
        }

        $password = (string) time();

        return User::query()->create([
            'user_name' => $userName,
            'user_nick_name' => trim((string) ($oauthInfo['name'] ?? '')),
            'user_pwd' => md5($password),
            'group_id' => '2',
            'user_points' => (int) config('maccms.user.reg_points', 0),
            'user_status' => (int) config('maccms.user.reg_status', 1),
            'user_reg_time' => time(),
            'user_reg_ip' => function_exists('mac_get_ip_long') ? (int) mac_get_ip_long() : 0,
            'user_random' => md5((string) random_int(10000000, 99999999)),
            'user_portrait' => (string) ($oauthInfo['head'] ?? ''),
            'user_portrait_thumb' => (string) ($oauthInfo['head'] ?? ''),
            $column => $openid,
        ]);
    }

    protected function getEnabledPaymentMethods(): array
    {
        $labels = [
            'alipay' => '支付宝',
            'weixin' => '微信支付',
            'codepay' => '码支付',
            'zhapay' => '幻兮支付',
            'epay' => '易支付',
        ];

        $methods = [];
        foreach ($labels as $payment => $label) {
            $class = $this->getPaymentClass($payment);
            if ($class === null || !class_exists($class)) {
                continue;
            }

            $config = (array) config('maccms.pay.' . $payment, []);
            $enabled = match ($payment) {
                'alipay' => trim((string) ($config['appid'] ?? '')) !== '' && trim((string) ($config['account'] ?? '')) !== '',
                'weixin', 'codepay', 'zhapay', 'epay' => trim((string) ($config['appid'] ?? '')) !== '',
                default => false,
            };

            if ($enabled) {
                $methods[$payment] = $label;
            }
        }

        return $methods;
    }

    protected function getPaymentClass(string $payment): ?string
    {
        $payment = strtolower(trim($payment));
        $map = [
            'alipay' => 'App\\Libraries\\Pay\\Alipay',
            'weixin' => 'App\\Libraries\\Pay\\Weixin',
            'codepay' => 'App\\Libraries\\Pay\\Codepay',
            'zhapay' => 'App\\Libraries\\Pay\\Zhapay',
            'epay' => 'App\\Libraries\\Pay\\Epay',
        ];

        return $map[$payment] ?? null;
    }

    protected function hydrateLegacyPayGlobals(Request $request): void
    {
        $GLOBALS['config'] = config('maccms');
        $GLOBALS['config']['pay'] = config('maccms.pay', []);
        $GLOBALS['http_type'] = $request->getScheme() . '://';
    }

    protected function buildAlipaySubmitHtml(object $handler, array $user, array $order): string
    {
        $data = [
            'service' => 'create_direct_pay_by_user',
            'payment_type' => '1',
            'quantity' => '1',
            '_input_charset' => 'utf-8',
            'partner' => trim((string) config('maccms.pay.alipay.appid', '')),
            'seller_email' => trim((string) config('maccms.pay.alipay.account', '')),
            'out_trade_no' => (string) ($order['order_code'] ?? ''),
            'notify_url' => url('/index.php/payment/notify/pay_type/alipay'),
            'return_url' => url('/index.php/payment/notify/pay_type/alipay'),
            'subject' => '积分充值（UID:' . ($user['user_id'] ?? 0) . ')',
            'total_fee' => sprintf('%.2f', (float) ($order['order_price'] ?? 0)),
        ];

        $para = method_exists($handler, 'buildRequestPara') ? $handler->buildRequestPara($data) : $data;
        $html = "<form id='alipaysubmit' name='alipaysubmit' action='https://mapi.alipay.com/gateway.do?_input_charset=utf-8' method='POST'>";
        foreach ($para as $key => $val) {
            $html .= "<input type='hidden' name='" . e((string) $key) . "' value='" . e((string) $val) . "'/>";
        }
        $html .= "<input type='submit' value='正在提交'></form>";
        $html .= "<script>document.forms['alipaysubmit'].submit();</script>";

        return $html;
    }

    protected function buildCodepayUrl(array $order, array $param): string
    {
        $payType = !empty($param['paytype']) ? (int) $param['paytype'] : 1;
        $data = [
            'id' => trim((string) config('maccms.pay.codepay.appid', '')),
            'type' => $payType,
            'price' => (float) ($order['order_price'] ?? 0),
            'pay_id' => (string) ($order['order_code'] ?? ''),
            'notify_url' => url('/index.php/payment/notify/pay_type/codepay'),
            'return_url' => url('/index.php/payment/notify/pay_type/codepay'),
            'debug' => 1,
            'ac' => trim((string) config('maccms.pay.codepay.ac', '')),
            'param' => '',
        ];

        ksort($data);
        $sign = [];
        $urls = [];
        foreach ($data as $key => $value) {
            if ($value === '' || $key === 'sign') {
                continue;
            }
            $sign[] = $key . '=' . $value;
            $urls[] = $key . '=' . urlencode((string) $value);
        }

        return 'https://api.xiuxiu888.com/creat_order/?' . implode('&', $urls) . '&sign=' . md5(implode('&', $sign) . trim((string) config('maccms.pay.codepay.appkey', '')));
    }

    protected function buildZhapayUrl(array $order, array $param): string
    {
        $payType = !empty($param['paytype']) ? (int) $param['paytype'] : 1;
        $data = [
            'mch_uid' => trim((string) config('maccms.pay.zhapay.appid', '')),
            'pay_type_id' => $payType,
            'total_fee' => (float) ($order['order_price'] ?? 0),
            'out_trade_no' => (string) ($order['order_code'] ?? ''),
            'notify_url' => url('/index.php/payment/notify/pay_type/zhapay'),
            'return_url' => url('/index.php/payment/notify/pay_type/zhapay'),
            'debug' => 1,
            'mepay_type' => trim((string) config('maccms.pay.zhapay.act', '')),
            'return_type' => 1,
            'param' => '',
        ];

        ksort($data);
        $sign = [];
        $urls = [];
        foreach ($data as $key => $value) {
            if ($value === '' || $key === 'sign') {
                continue;
            }
            $sign[] = $key . '=' . $value;
            $urls[] = $key . '=' . urlencode((string) $value);
        }

        return 'https://www.zhapay.com/mapay.html?' . implode('&', $urls) . '&sign=' . md5(implode('&', $sign) . trim((string) config('maccms.pay.zhapay.appkey', '')));
    }

    protected function respondUserAction(Request $request, array $payload, string $route, array $params = [])
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($payload);
        }

        $redirect = redirect()->route($route, $params);
        if ((int) ($payload['code'] ?? 0) === 1) {
            return $redirect->with('success', (string) ($payload['msg'] ?? ''));
        }

        return $redirect->withErrors(['msg' => (string) ($payload['msg'] ?? '')])->withInput();
    }

    protected function respondUserFlow(Request $request, array $payload, string $successRoute, string $errorRoute, array $successParams = [], array $errorParams = [])
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($payload);
        }

        $success = (int) ($payload['code'] ?? 0) === 1;
        $redirect = redirect()->route($success ? $successRoute : $errorRoute, $success ? $successParams : $errorParams);
        if ($success) {
            return $redirect->with('success', (string) ($payload['msg'] ?? ''));
        }

        return $redirect->withErrors(['msg' => (string) ($payload['msg'] ?? '')])->withInput();
    }

    protected function normalizeIds(string $ids): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn ($value) => ($id = abs((int) $value)) > 0 ? $id : null,
            explode(',', $ids)
        ))));
    }
}
