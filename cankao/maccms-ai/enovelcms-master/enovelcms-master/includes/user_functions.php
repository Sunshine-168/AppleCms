<?php
/**
 * 用户中心所有逻辑处理函数
 */

function user_index_handler() {
    global $db, $lang;
    $userId = $_SESSION['user_id'];
    $user = $db->fetch($db->query("SELECT * FROM users WHERE id=?", [$userId]));
    if (!$user) {
        session_destroy();
        redirect(BASE_URL . '/user/login');
    }

    $vipActive = ($user['vip_level'] == 1 && ($user['vip_expire'] === null || strtotime($user['vip_expire']) > time()));
    $bookshelfCount = $db->fetch($db->query("SELECT COUNT(*) as cnt FROM bookshelf WHERE user_id=?", [$userId]))['cnt'];
    $historyCount = $db->fetch($db->query("SELECT COUNT(*) as cnt FROM reading_history WHERE user_id=?", [$userId]))['cnt'];
    $todaySigned = $db->fetch($db->query("SELECT id FROM gold_logs WHERE user_id = ? AND type = 'sign' AND DATE(created_at) = CURDATE()", [$userId])) ? true : false;

    return [
        'user' => $user,
        'vipActive' => $vipActive,
        'bookshelfCount' => $bookshelfCount,
        'historyCount' => $historyCount,
        'todaySigned' => $todaySigned,
        'pageTitle' => $lang->get('user_center')
    ];
}

function user_profile_handler() {
    global $db, $lang;
    $userId = $_SESSION['user_id'];
    $user = $db->fetch($db->query("SELECT * FROM users WHERE id=?", [$userId]));
    if (!$user) {
        session_destroy();
        redirect(BASE_URL . '/user/login');
    }

    $vipActive = ($user['vip_level'] == 1 && ($user['vip_expire'] === null || strtotime($user['vip_expire']) > time()));

    $message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = input('email', '', 'POST');
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = $lang->get('email_format_error');
        } else {
            $db->query("UPDATE users SET email=? WHERE id=?", [$email, $userId]);
            $message = $lang->get('profile_update_success');
            $user = $db->fetch($db->query("SELECT * FROM users WHERE id=?", [$userId]));
        }
        $oldPass = input('old_password', '', 'POST');
        $newPass = input('new_password', '', 'POST');
        $confirmPass = input('confirm_password', '', 'POST');
        if (!empty($oldPass) && !empty($newPass)) {
            if (!password_verify($oldPass, $user['password'])) {
                $message = $lang->get('old_password_error');
            } elseif (strlen($newPass) < 6) {
                $message = $lang->get('password_too_short');
            } elseif ($newPass !== $confirmPass) {
                $message = $lang->get('password_mismatch');
            } else {
                $hashed = password_hash($newPass, PASSWORD_DEFAULT);
                $db->query("UPDATE users SET password=? WHERE id=?", [$hashed, $userId]);
                $message = $lang->get('password_changed');
                unset($_SESSION['user_id']);
                redirect(BASE_URL . '/user/login');
            }
        }
    }
    return [
        'user' => $user,
        'vipActive' => $vipActive,
        'message' => $message,
        'pageTitle' => $lang->get('profile')
    ];
}

function user_bookshelf_handler() {
    global $db, $lang;
    $userId = $_SESSION['user_id'];
    $user = $db->fetch($db->query("SELECT * FROM users WHERE id=?", [$userId]));
    if (!$user) {
        session_destroy();
        redirect(BASE_URL . '/user/login');
    }
    $vipActive = ($user['vip_level'] == 1 && ($user['vip_expire'] === null || strtotime($user['vip_expire']) > time()));

    $books = $db->fetchAll($db->query("SELECT b.*, n.title, n.cover, n.author, c.title as last_chapter_title 
        FROM bookshelf b 
        JOIN novels n ON b.novel_id=n.id 
        LEFT JOIN chapters c ON b.last_read_chapter=c.id 
        WHERE b.user_id=? ORDER BY b.updated_at DESC", [$userId]));

    return [
        'user' => $user,
        'vipActive' => $vipActive,
        'books' => $books,
        'pageTitle' => $lang->get('bookshelf')
    ];
}

function user_history_handler() {
    global $db, $lang;
    $userId = $_SESSION['user_id'];
    $user = $db->fetch($db->query("SELECT * FROM users WHERE id=?", [$userId]));
    if (!$user) {
        session_destroy();
        redirect(BASE_URL . '/user/login');
    }
    $vipActive = ($user['vip_level'] == 1 && ($user['vip_expire'] === null || strtotime($user['vip_expire']) > time()));

    $history = $db->fetchAll($db->query("SELECT h.*, n.title as novel_title, c.title as chapter_title 
        FROM reading_history h 
        JOIN novels n ON h.novel_id=n.id 
        JOIN chapters c ON h.chapter_id=c.id 
        WHERE h.user_id=? ORDER BY h.read_at DESC LIMIT 50", [$userId]));

    return [
        'user' => $user,
        'vipActive' => $vipActive,
        'history' => $history,
        'pageTitle' => $lang->get('history')
    ];
}

function user_gold_handler() {
    global $db, $lang;
    $userId = $_SESSION['user_id'];
    $user = $db->fetch($db->query("SELECT * FROM users WHERE id=?", [$userId]));
    if (!$user) {
        session_destroy();
        redirect(BASE_URL . '/user/login');
    }
    $vipActive = ($user['vip_level'] == 1 && ($user['vip_expire'] === null || strtotime($user['vip_expire']) > time()));

    $logs = $db->fetchAll($db->query("SELECT * FROM gold_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 50", [$userId]));

    return [
        'user' => $user,
        'vipActive' => $vipActive,
        'logs' => $logs,
        'pageTitle' => $lang->get('gold_log')
    ];
}

function user_sign_handler() {
    global $db, $lang;
    $userId = $_SESSION['user_id'];
    $message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = doSign($userId);
        if ($result['success']) {
            $message = sprintf($lang->get('sign_success'), $result['reward']) . '，' . sprintf($lang->get('continuous_sign'), $result['continuous_days']);
        } else {
            $message = $result['message'];
        }
    }
    $user = $db->fetch($db->query("SELECT * FROM users WHERE id=?", [$userId]));
    if (!$user) {
        session_destroy();
        redirect(BASE_URL . '/user/login');
    }
    $vipActive = ($user['vip_level'] == 1 && ($user['vip_expire'] === null || strtotime($user['vip_expire']) > time()));

    $todaySigned = $db->fetch($db->query("SELECT id FROM gold_logs WHERE user_id = ? AND type = 'sign' AND DATE(created_at) = CURDATE()", [$userId])) ? true : false;
    $rewardPreview = getSignReward($userId);
    return [
        'user' => $user,
        'vipActive' => $vipActive,
        'message' => $message,
        'todaySigned' => $todaySigned,
        'rewardPreview' => $rewardPreview,
        'pageTitle' => $lang->get('sign_today')
    ];
}

function user_vip_handler() {
    global $db, $lang;
    require_once ROOT_PATH . 'includes/Payment/YiPay.php';
    
    $userId = $_SESSION['user_id'];
    $user = $db->fetch($db->query("SELECT * FROM users WHERE id=?", [$userId]));
    if (!$user) {
        session_destroy();
        redirect(BASE_URL . '/user/login');
    }
    
    $vipActive = ($user['vip_level'] == 1 && ($user['vip_expire'] === null || strtotime($user['vip_expire']) > time()));
    $message = '';
    
    if (isset($_POST['exchange_vip'])) {
        $months = (int)$_POST['months'];
        if ($months <= 0) {
            $message = $lang->get('select_valid_months');
        } else {
            $result = exchangeVipWithGold($userId, $months);
            $message = $result['message'];
            if ($result['success']) {
                $user = $db->fetch($db->query("SELECT * FROM users WHERE id=?", [$userId]));
                $vipActive = ($user['vip_level'] == 1 && ($user['vip_expire'] === null || strtotime($user['vip_expire']) > time()));
            }
        }
    }
    
    if (isset($_POST['pay_vip'])) {
        $months = (int)$_POST['months'];
        $payType = input('pay_type', 'alipay', 'POST');
        $pricePerMonth = (float)getSetting('vip_price_per_month', $db);
        
        if ($pricePerMonth <= 0) {
            die($lang->get('payerror'));
        }
        if ($months <= 0) {
            die($lang->get('invalid_params'));
        }
        $amount = $months * $pricePerMonth;
        $outTradeNo = generateOutTradeNo();
        
        $db->query("INSERT INTO orders (out_trade_no, user_id, money, type, months) VALUES (?,?,?,?,?)",
            [$outTradeNo, $userId, $amount, 'vip', $months]);
        
        $apiUrl = getSetting('yipay_api_url', $db);
        $pid = getSetting('yipay_pid', $db);
        $key = getSetting('yipay_key', $db);
        if (empty($apiUrl) || empty($pid) || empty($key)) {
            die($lang->get('payerror'));
        }
        $pay = new YiPay($apiUrl, $pid, $key);
        $order = [
            'type' => $payType,
            'out_trade_no' => $outTradeNo,
            'notify_url' => BASE_URL . '/api/pay_notify.php',
            'return_url' => BASE_URL . '/api/pay_return.php',
            'name' => sprintf($lang->get('vip_pay_description'), $pricePerMonth, $months),
            'money' => $amount,
            'sitename' => getSetting('site_name', $db) ?: 'Novel Site'
        ];
        echo $pay->submit($order);
        exit;
    }
    
    $pricePerMonth = (float)getSetting('vip_price_per_month', $db);
    $goldPerMonth = (int)getSetting('vip_gold_per_month', $db);
    
    return [
        'user' => $user,
        'vipActive' => $vipActive,
        'message' => $message,
        'pricePerMonth' => $pricePerMonth,
        'goldPerMonth' => $goldPerMonth,
        'pageTitle' => $lang->get('vip_center')
    ];
}

/* ===== 修改：user_login_handler 支持邮箱登录 ===== */
function user_login_handler() {
    global $db, $lang;
    if (isLoggedIn()) {
        redirect(BASE_URL . '/user/');
    }
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim(input('username', '', 'POST'));
        $password = input('password', '', 'POST');
        $captcha = input('captcha', '', 'POST');
        
        if (!verify_captcha($captcha)) {
            $error = $lang->get('captcha_error');
            clear_captcha();
        } else {
            clear_captcha();
            $mode = getSetting('register_mode', $db) ?: 'normal';
            // 如果是邮箱模式且输入包含@，视为邮箱登录
            if ($mode === 'email' && strpos($username, '@') !== false) {
                $user = $db->fetch($db->query("SELECT * FROM users WHERE email = ?", [$username]));
            } else {
                $user = $db->fetch($db->query("SELECT * FROM users WHERE username = ?", [$username]));
            }
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                redirect(BASE_URL . '/user/');
            } else {
                $error = $lang->get('login_failed');
            }
        }
    }
    return [
        'error' => $error,
        'pageTitle' => $lang->get('login')
    ];
}

function user_register_handler() {
    global $db, $lang;
    if (isLoggedIn()) {
        redirect(BASE_URL . '/user/');
    }
    $mode = getSetting('register_mode', $db) ?: 'normal';

    if (isset($_GET['send_code']) && isset($_GET['email'])) {
        if ($mode !== 'email') {
            echo json_encode(['code' => 0, 'msg' => $lang->get('operation_failed')]);
            exit;
        }
        header('Content-Type: application/json');
        $email = trim($_GET['email']);
        $captcha = input('captcha', '', 'GET');
        
        if (!verify_captcha($captcha)) {
            echo json_encode(['code' => 0, 'msg' => $lang->get('captcha_error')]);
            exit;
        }
        clear_captcha();
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['code' => 0, 'msg' => $lang->get('email_format_error')]);
            exit;
        }
        if ($db->fetch($db->query("SELECT id FROM users WHERE email = ?", [$email]))) {
            echo json_encode(['code' => 0, 'msg' => $lang->get('email_already_registered')]);
            exit;
        }
        $result = sendVerifyCode($email, 'register');
        if ($result['success']) {
            echo json_encode(['code' => 1, 'msg' => $lang->get('code_sent')]);
        } else {
            echo json_encode(['code' => 0, 'msg' => $result['message']]);
        }
        exit;
    }

    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim(input('username', '', 'POST'));
        $password = input('password', '', 'POST');
        $email = trim(input('email', '', 'POST'));
        $code = input('code', '', 'POST');
        $captcha = input('captcha', '', 'POST');
        
        if (!verify_captcha($captcha)) {
            $error = $lang->get('captcha_error');
            clear_captcha();
        } elseif (strlen($username) < 3) {
            $error = $lang->get('username_too_short');
        } elseif ($db->fetch($db->query("SELECT id FROM users WHERE username = ?", [$username]))) {
            $error = $lang->get('username_exists');
        } else {
            if ($mode === 'email') {
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = $lang->get('email_format_error');
                } elseif ($db->fetch($db->query("SELECT id FROM users WHERE email = ?", [$email]))) {
                    $error = $lang->get('email_already_registered');
                } elseif (!verifyCode($email, $code)) {
                    $error = $lang->get('code_error');
                }
            } else {
                if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = $lang->get('email_format_error');
                } elseif (!empty($email) && $db->fetch($db->query("SELECT id FROM users WHERE email = ?", [$email]))) {
                    $error = $lang->get('email_already_registered');
                }
                if (empty($email)) $email = '';
            }
            
            if (empty($error)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $db->query("INSERT INTO users (username, password, email, gold) VALUES (?,?,?,?)", 
                    [$username, $hash, $email, 0]);
                $_SESSION['user_id'] = $db->lastInsertId();
                $_SESSION['username'] = $username;
                redirect(BASE_URL . '/user/');
            }
        }
    }
    return [
        'error' => $error,
        'pageTitle' => $lang->get('register')
    ];
}

function user_forgot_handler() {
    global $db, $lang;
    if (isLoggedIn()) {
        redirect(BASE_URL . '/user/');
    }
    $step = isset($_GET['step']) ? $_GET['step'] : 'request';
    $message = '';
    $error = '';
    
    if ($step == 'send_code' && isset($_GET['email'])) {
        $email = trim($_GET['email']);
        $captcha = input('captcha', '', 'GET');
        
        if (!verify_captcha($captcha)) {
            $error = $lang->get('captcha_error');
            clear_captcha();
            include ROOT_PATH . 'templates/header.php';
            echo '<div class="container"><p class="error">' . h($error) . '</p><a href="forgot.php">' . $lang->get('back') . '</a></div>';
            include ROOT_PATH . 'templates/footer.php';
            exit;
        }
        clear_captcha();
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = $lang->get('email_format_error');
        } elseif (!$db->fetch($db->query("SELECT id FROM users WHERE email=?", [$email]))) {
            $error = $lang->get('user_not_exists');
        } else {
            $result = sendVerifyCode($email, 'reset');
            if ($result['success']) {
                $message = $lang->get('verification_code_sent');
            } else {
                $error = $result['message'];
            }
        }
        include ROOT_PATH . 'templates/header.php';
        echo '<div class="container">';
        if ($message) echo '<p class="success">' . h($message) . '</p>';
        if ($error) echo '<p class="error">' . h($error) . '</p>';
        echo '<a href="forgot.php">' . $lang->get('back') . '</a></div>';
        include ROOT_PATH . 'templates/footer.php';
        exit;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = input('email', '', 'POST');
        $code = input('code', '', 'POST');
        $captcha = input('captcha', '', 'POST');
        
        if (!verify_captcha($captcha)) {
            $error = $lang->get('captcha_error');
            clear_captcha();
        } else {
            clear_captcha();
            if (verifyCode($email, $code)) {
                $result = resetPassword($email);
                if ($result['success']) {
                    $message = $result['message'];
                    $step = 'done';
                } else {
                    $error = $result['message'];
                }
            } else {
                $error = $lang->get('code_error');
            }
        }
    }
    return [
        'step' => $step,
        'message' => $message,
        'error' => $error,
        'pageTitle' => $lang->get('forgot_password_title')
    ];
}

function user_logout_handler() {
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    redirect(BASE_URL . '/');
}