<?php
/**
 * 数据库操作类
 * 支持 PDO 和 MySQLi 两种驱动
 */
class Database {
    private $connection;
    private $type;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USER, DB_PASS);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->type = 'pdo';
        } catch (PDOException $e) {
            // 降级使用 MySQLi
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($this->connection->connect_error) {
                throw new Exception("数据库连接失败: " . $this->connection->connect_error);
            }
            $this->connection->set_charset(DB_CHARSET);
            $this->type = 'mysqli';
        }
    }

    /**
     * 执行查询语句
     * @param string $sql SQL语句
     * @param array $params 参数绑定
     * @return mixed 结果集对象
     */
    public function query($sql, $params = []) {
        if ($this->type === 'pdo') {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } else {
            $stmt = $this->connection->prepare($sql);
            if ($stmt && !empty($params)) {
                $types = '';
                $bindParams = [];
                foreach ($params as $key => $value) {
                    if (is_int($value)) {
                        $types .= 'i';
                    } elseif (is_float($value)) {
                        $types .= 'd';
                    } else {
                        $types .= 's';
                    }
                    $bindParams[] = &$params[$key];
                }
                array_unshift($bindParams, $types);
                call_user_func_array([$stmt, 'bind_param'], $bindParams);
                $stmt->execute();
                return $stmt->get_result();
            } elseif ($stmt) {
                $stmt->execute();
                return $stmt->get_result();
            }
            return $this->connection->query($sql);
        }
    }

    /**
     * 获取单行结果
     * @param mixed $result 结果集
     * @return array|null
     */
    public function fetch($result) {
        if ($this->type === 'pdo') {
            return $result->fetch(PDO::FETCH_ASSOC);
        } else {
            return $result->fetch_assoc();
        }
    }

    /**
     * 获取全部结果
     * @param mixed $result 结果集
     * @return array
     */
    public function fetchAll($result) {
        if ($this->type === 'pdo') {
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    }

    /**
     * 获取最后插入ID
     * @return int|string
     */
    public function lastInsertId() {
        return $this->type === 'pdo' ? $this->connection->lastInsertId() : $this->connection->insert_id;
    }
}