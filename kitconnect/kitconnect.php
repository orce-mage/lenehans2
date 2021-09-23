<?php
ini_set('display_errors', false);
date_default_timezone_set('America/Los_Angeles');

class NC_Kitconnect {

    public $action;

    public function run() {
        if (empty($_GET)) {
            ?>
            <html><head><title>Next-Cart - The Best Shopping Cart Migration Service</title><style type="text/css">body{font-family:"Lato",Helvetica,sans-serif;font-weight:400}.confirm-box-container{display:flex;position:fixed;padding:10px;top:0;left:0;bottom:0;right:0;overflow-y:auto;background:#bcd1ff;z-index:666;align-items:center}.confirm-box{background-color:#fff;width:478px;max-width:100%;padding:17px;border-radius:5px;text-align:center;position:relative;margin:auto;overflow:hidden}.confirm-box h2{color:#555;margin-top:32px;margin-bottom:32px}.confirm-box span{display:block;color:#777;margin-bottom:20px;font-size:21px}.success-checkmark{margin:20px 0;text-align:center}.success-checkmark .check-icon{width:80px;height:80px;position:relative;display:inline-block;border-radius:50%;box-sizing:content-box;border:4px solid #4CAF50}.success-checkmark .check-icon::before{top:3px;left:-2px;width:30px;transform-origin:100% 50%;border-radius:100px 0 0 100px}.success-checkmark .check-icon::after{top:0;left:30px;width:60px;transform-origin:0 50%;border-radius:0 100px 100px 0;animation:rotate-circle 4.25s ease-in}.success-checkmark .check-icon::before, .success-checkmark .check-icon::after{content:'';height:100px;position:absolute;background:#FFF;transform:rotate(-45deg)}.success-checkmark .check-icon .icon-line{height:5px;background-color:#4CAF50;display:block;border-radius:2px;position:absolute;z-index:10}.success-checkmark .check-icon .icon-line.line-tip{top:46px;left:14px;width:25px;transform:rotate(45deg);animation:icon-line-tip 0.75s}.success-checkmark .check-icon .icon-line.line-long{top:38px;right:8px;width:47px;transform:rotate(-45deg);animation:icon-line-long 0.75s}.success-checkmark .check-icon .icon-circle{top:-4px;left:-4px;z-index:10;width:80px;height:80px;border-radius:50%;position:absolute;box-sizing:content-box;border:4px solid rgba(76, 175, 80, .5)}.success-checkmark .check-icon .icon-fix{top:8px;width:5px;left:26px;z-index:1;height:85px;position:absolute;transform:rotate(-45deg);background-color:#FFF}@keyframes rotate-circle{0%{transform:rotate(-45deg)}5%{transform:rotate(-45deg)}12%{transform:rotate(-405deg)}100%{transform:rotate(-405deg)}}@keyframes icon-line-tip{0%{width:0;left:1px;top:19px}54%{width:0;left:1px;top:19px}70%{width:50px;left:-8px;top:37px}84%{width:17px;left:21px;top:48px}100%{width:25px;left:14px;top:45px}}@keyframes icon-line-long{0%{width:0;right:46px;top:54px}65%{width:0;right:46px;top:54px}84%{width:55px;right:0px;top:35px}100%{width:47px;right:8px;top:38px}}</style></head><body><div class="confirm-box-container"><div class="confirm-box"><div class="success-checkmark"><div class="check-icon"> <span class="icon-line line-tip"></span> <span class="icon-line line-long"></span><div class="icon-circle"></div><div class="icon-fix"></div></div></div><h2>Hi! I'm Kitconnect</h2> <span>Your connection was installed successfully!</span></div></div></body></html>
            <?php
            return;
        }
        if (!$this->checkToken()) {
            NC_Response::error('Invalid secret token');
            return;
        }

        $action = NC_Action::instance();
        if (!$action) {
            NC_Response::error('Action not found');
            return;
        }
        $action->run();
        return;
    }

    public function checkToken() {
        if (isset($_GET['token']) && md5($_GET['token']) == md5(NC_TOKEN)) {
            return true;
        }
        return false;
    }

}

abstract class NC_Action {

    public static $instance = null;

    abstract public function run();

    public static function instance() {
        if (is_null(self::$instance)) {
            $class = self::getClass();
            if (!$class) {
                return null;
            } else {
                self::$instance = new $class();
            }
        }
        return self::$instance;
    }

    public static function getClass() {
        if (isset($_GET['action']) && $_GET['action']) {
            $class = __CLASS__ . '_' . ucfirst($_GET['action']);
            if (class_exists($class)) {
                return $class;
            } else {
                return null;
            }
        }
        return null;
    }

    public function getParams($key, $params, $default = null) {
        return isset($params[$key]) ? $params[$key] : $default;
    }

}

class NC_Action_Check extends NC_Action {

    public function run() {
        $cart = NC_Cart::instance(true);
        if (!$cart) {
            NC_Response::error('Cart type is not specified or declared.');
            return;
        }
        $data['image_category'] = $cart->imageDirCategory;
        $data['image_product'] = $cart->imageDirProduct;
        $data['image_manufacturer'] = $cart->imageDirManufacturer;
        $data['table_prefix'] = $cart->tablePrefix;
        $data['version'] = $cart->version;
        $data['charset'] = $cart->charset;
        $data['cookie_key'] = $cart->cookie_key;
        $data['extend'] = $cart->extend;
        $dbConnect = NC_Db::getInstance($cart);
        if ($dbConnect->getError()) {
            $data['connect'] = array(
                'result' => 'error',
                'msg' => 'Cannot connect to database. Error: ' . $dbConnect->getError()
            );
        } else {
            $data['connect'] = array(
                'result' => 'success',
                'msg' => 'Successfully connect to database!'
            );
        }
        NC_Response::success('Cart type ' . $_GET['cart'] . ' is verified!', $data);
        return;
    }

}

class NC_Action_Query extends NC_Action {

    public function run() {
        $cart = NC_Cart::instance();
        if (!$cart) {
            NC_Response::error('Cart type is not specified or declared.');
            return;
        }
        $dbConnect = NC_Db::getInstance($cart);
        if ($dbConnect->getError()) {
            NC_Response::error('Cannot connect to database. Error: ' . $dbConnect->getError());
            return;
        }
        if (isset($_REQUEST['query'])) {
            $queries = @unserialize(base64_decode($_REQUEST['query']));
            if (isset($_REQUEST['serialize']) && $_REQUEST['serialize'] && $queries !== false) {
                foreach ($queries as $key => $query) {
                    if (is_array($query) && isset($query['type'])) {
                        $params = isset($query['params']) ? $query['params'] : null;
                        $data[$key] = $dbConnect->processQuery($query['type'], $query['query'], $params);
                    } else {
                        $data[$key] = $dbConnect->processQuery('select', $query);
                    }
                }
            } elseif ($queries !== false) {
                $query = $queries;
                $params = isset($query['params']) ? $query['params'] : null;
                $data = $dbConnect->processQuery($query['type'], $query['query'], $params);
            } else {
                $query = base64_decode($_REQUEST['query']);
                $data = $dbConnect->processQuery('select', $query);
            }
            if ($data === false) {
                NC_Response::error('Cannot execute queries. Error: ' . $dbConnect->getError() . '. QUERY : ' . $query['query']);
                return;
            }
            NC_Response::success('', $data);
            return;
        } else {
            NC_Response::error('Queries is empty.');
            return;
        }
    }

}

class NC_Action_File extends NC_Action {

    public function run() {
        $data = array();
        if (isset($_REQUEST['files'])) {
            $files = unserialize(base64_decode($_REQUEST['files']));
            foreach ($files as $key => $file) {
                $params = isset($file['params']) ? $file['params'] : array();
                $data[$key] = $this->processFile($file['type'], $file['path'], $params);
            }
        }
        NC_Response::success('', $data);
        return;
    }

    public function processFile($type, $path, $params = array()) {
        $result = false;
        switch ($type) {
            case 'download':
                $result = $this->download($path, $params);
                break;
            case 'delete':
                $result = $this->delete($path, $params);
                break;
            case 'info':
                $result = $this->info($path);
                break;
            default:
                break;
        }
        return $result;
    }

    public function download($path, $params = array(), $time = 5) {
        $result = false;
        if (!$time) {
            return $result;
        }
        $override = $this->getParams('override', $params);
        $rename = $this->getParams('rename', $params);
        $url = $this->getParams('url', $params);
        $list_images = $this->getParams('list_images', $params);
        if (!$url) {
            return $result;
        }
        if ($this->exists($path)) {
            if ($rename) {
                $path = $this->rename($path);
            } else {
                if (!$override) {
                    return $result;
                }
                $delete_file = $this->delete($path);
                if (!$delete_file) {
                    return $result;
                }
            }
        }
        $full_path = $this->getRealPath($path);
        $check_extension = pathinfo($full_path, PATHINFO_EXTENSION);
        $executable_files = array('sh', 'asp', 'cgi', 'php', 'ph', 'phtm', 'shtm', 'pl', 'py', 'jsp');
        foreach ($executable_files as $e_ext) {
            if (stripos($check_extension, $e_ext) !== false) {
                return $result;
            }
        }
        $this->createParentDir($full_path);
        $data = @file_put_contents($full_path, fopen($url, 'r'));
        if ($data) {
            $result = $path;
        } else {
            $fp = fopen($full_path, 'w');
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:86.0) Gecko/20100101 Firefox/86.0');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            if (curl_errno($ch)) {
                return $result;
            }
            curl_close($ch);
            fclose($fp);
            if (@filesize($full_path) > 0) {
                $result = $path;
            } else {
                sleep(1);
                $time--;
                $result = $this->download($path, $params, $time);
            }
        }
        if ($list_images && $result) {
            foreach ($list_images as $image) {
                $desc_img = $this->getRealPath($image['path']);
                list($src_width, $src_height, $type) = getimagesize($full_path);
                $ratio = $image['width'] / $src_width;
                if ($image['height'] / $src_height < $ratio) {
                    $ratio = $image['height'] / $src_height;
                }
                $destinationWidth = $nextWidth = round($src_width * $ratio);
                $destinationHeight = $nextHeight = round($src_height * $ratio);
                $dst_x = $dst_y = 0;
                if ($image['height'] >= $src_height && $image['width'] >= $src_width) {
                    if ($image['width'] > $src_width) {
                        $dst_x = ($destinationWidth - $src_width) / 2;
                        $nextWidth = $src_width;
                    }
                    if ($image['height'] > $src_height) {
                        $dst_y = ($destinationHeight - $src_height) / 2;
                        $nextHeight = $src_height;
                    }
                }
                //imagecopyresized($desc_img, $full_path, 0, 0, 0, 0, $image['width'], $image['height'], $src_width, $src_height);
                $new_img = imagecreatetruecolor($destinationWidth, $destinationHeight);
                if ($type == IMAGETYPE_PNG) {
                    imagealphablending($new_img, false);
                    imagesavealpha($new_img, true);
                    $transparent = imagecolorallocatealpha($new_img, 255, 255, 255, 127);
                    imagefilledrectangle($new_img, 0, 0, $destinationWidth, $destinationHeight, $transparent);
                    $original_image = imagecreatefrompng($full_path);
                } else {
                    $white = imagecolorallocate($new_img, 255, 255, 255);
                    imagefilledrectangle($new_img, 0, 0, $destinationWidth, $destinationHeight, $white);
                    $original_image = imagecreatefromjpeg($full_path);
                }
                $new_path = $desc_img;
                imagecopyresized($new_img, $original_image, (int) $dst_x, (int) $dst_y, '0', '0', $nextWidth, $nextHeight, $src_width, $src_height);
                if ($type == IMAGETYPE_PNG) {
                    imagepng($new_img, $new_path);
                } else {
                    imagejpeg($new_img, $new_path, 100);
                }
            }
        }
//        if(!$result){
//            $time--;
//            $result = $this->download($path, $params, $time);
//        }
        return $result;
    }

    public function info($path) {
        $full_path = $this->getRealPath($path);
        if (file_exists($full_path)) {
            return @getimagesize($full_path);
        }
        return false;
    }

    public function exists($path, $params = array()) {
        $full_path = $this->getRealPath($path);
        return file_exists($full_path);
    }

    public function rename($path, $params = array()) {
        $path = ltrim($path, '/');
        $new_path = $path;
        $full_path = $this->getRealPath($new_path);
        $i = 1;
        while (file_exists($full_path)) {
            $new_path = $this->createFileSuffix($path, $i);
            $full_path = $this->getRealPath($new_path);
            $i++;
        }
        return $new_path;
    }

    public function delete($path, $params = array()) {
        $result = true;
        if (!$this->exists($path)) {
            return $result;
        }
        $full_path = $this->getRealPath($path);
        $result = @unlink($full_path);
        return $result;
    }

    public function content($path, $params = array()) {
        $result = '';
        $full_path = $this->getRealPath($path);
        if (!$this->exists($path)) {
            return $result;
        }
        $result = @file_get_contents($full_path);
        return $result;
    }

    public function copy($path, $params = array()) {
        $result = false;
        $override = $this->getParams('override', $params);
        $copy_path = $this->getParams('copy', $params);
        if (!$copy_path) {
            return $result;
        }
        if (!$this->exists($path)) {
            return $result;
        }
        if ($this->exists($copy_path)) {
            if (!$override) {
                return $result;
            }
            $delete_file = $this->delete($copy_path);
            if (!$delete_file) {
                return $result;
            }
        }
        $full_path = $this->getRealPath($path);
        $full_copy_path = $this->getRealPath($copy_path);
        $this->createParentDir($full_copy_path);
        $result = @copy($full_path, $full_copy_path);
        return $result;
    }

    public function move($path, $params = array()) {
        $result = false;
        $override = $this->getParams('override', $params);
        $move_path = $this->getParams('move', $params);
        if (!$move_path) {
            return $result;
        }
        if (!$this->exists($path)) {
            return $result;
        }
        if ($this->exists($move_path)) {
            if (!$override) {
                return $result;
            }
            $delete_file = $this->delete($move_path);
            if (!$delete_file) {
                return $result;
            }
        }
        $full_path = $this->getRealPath($path);
        $full_move_path = $this->getRealPath($move_path);
        $this->createParentDir($full_move_path);
        $result = rename($full_path, $full_move_path);
        return $result;
    }

    public function getRealPath($path) {
        $real_path = NC_STORE_BASE_DIR . ltrim($path, '/');
        if ($_GET['cart'] == 'magento' && !file_exists(NC_STORE_BASE_DIR . 'app/etc/local.xml') && !file_exists(NC_STORE_BASE_DIR . 'app/etc/env.php')) {
            $real_path = NC_STORE_BASE_DIR . '../' . ltrim($path, '/');
        }
        if ($_GET['cart'] == 'bagisto') {
            $real_path = NC_STORE_BASE_DIR . '../' . ltrim($path, '/');
        }
        return $real_path;
    }

    public function createParentDir($path, $mode = 0777) {
        $result = true;
        if (!is_dir(dirname($path))) {
            $result = @mkdir(dirname($path), 0777, true);
        }
        return $result;
    }

    public function createFileSuffix($file_path, $suffix, $character = '_') {
        $new_path = '';
        $dir_name = pathinfo($file_path, PATHINFO_DIRNAME);
        $file_name = pathinfo($file_path, PATHINFO_FILENAME);
        $file_ext = pathinfo($file_path, PATHINFO_EXTENSION);
        if ($dir_name && $dir_name != '.')
            $new_path .= $dir_name . '/';
        $new_path .= $file_name . $character . $suffix . '.' . $file_ext;
        return $new_path;
    }

}

class NC_Response {

    public static function displayResponse($result, $msg, $data) {
        $response = array();
        $response['result'] = $result;
        $response['msg'] = $msg;
        $response['data'] = $data;
        echo base64_encode(serialize($response));
    }

    public static function error($msg = null, $data = null) {
        self::displayResponse('error', $msg, $data);
    }

    public static function success($msg = null, $data = null) {
        self::displayResponse('success', $msg, $data);
    }

}

abstract class NC_Db {

    public static $instance = null;
    public static $servers = array();
    public $server = 'localhost';
    public $user = 'root';
    public $password = '';
    public $database = '';
    public $link = null;
    public $error = null;

    abstract public function connect();

    abstract public function query($query);

    abstract public function select($query);

    abstract public function insert($query, $params);

    abstract public function disconnect();

    public function __construct($server, $user, $password, $database, $connect = true) {
        $this->server = $server;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        if ($connect) {
            $this->connect();
        }
    }

    public function __destruct() {
        if ($this->link) {
            $this->disconnect();
        }
    }

    public static function getInstance($cart) {
        if (!self::$instance) {
            $class = self::getClass();
            self::$servers = array('server' => $cart->host, 'user' => $cart->username, 'password' => $cart->password, 'database' => $cart->database);
            self::$instance = new $class(
                    self::$servers['server'], self::$servers['user'], self::$servers['password'], self::$servers['database']
            );
        }
        return self::$instance;
    }

    public static function getClass() {
        $class = 'NC_MySQL';
        if (extension_loaded('mysqli')) {
            $class = 'NC_MySQLi';
        } elseif (PHP_VERSION_ID >= 50200 && extension_loaded('pdo_mysql')) {
            //$class = 'NC_PDO';
        }

        return $class;
    }

    public function getLink() {
        return $this->link;
    }

    public function getError() {
        return $this->getMsgError();
    }

    public function getMsgError() {
        return $this->error;
    }

    public function processQuery($type, $query, $params = null) {
        $result = null;
        switch ($type) {
            case 'select':
                $result = $this->select($query);
                break;
            case 'insert':
                $result = $this->insert($query, $params);
                break;
            case 'query':
                $result = $this->query($query);
                break;
            default:
                $result = $this->query($query);
                break;
        }
        return $result;
    }

}

class NC_MySQL extends NC_Db {

    public function connect() {
        if (!$this->link = @mysql_connect($this->server, $this->user, $this->password)) {
            $this->error = 'Link to database cannot be established.';
            return;
        }
        if (!mysql_select_db($this->database, $this->link)) {
            $this->error = 'The database selection cannot be made.';
            return;
        }
        if (!mysql_query('SET NAMES \'utf8\'', $this->link)) {
            $this->error = 'No utf-8 support. Please check your server configuration.';
            return;
        }
        return $this->link;
    }

    public function disconnect() {
        mysql_close($this->link);
    }

    public function query($sql) {
        return mysql_query($sql, $this->link);
    }

    public function insert($sql, $params) {
        $result = $this->query($sql);
        if ($result && isset($params['insert_id'])) {
            $result = mysql_insert_id($this->link);
        }
        return $result;
    }

    public function select($sql) {
        $data = array();
        $result = $this->query($sql);
        if (!$result || !is_resource($result)) {
            return false;
        }
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }

    public function getMsgError() {
        if (!$this->error) {
            $this->error = mysql_error($this->link);
        }
        return $this->error;
    }

}

class NC_MySQLi extends NC_Db {

    public function connect() {
        $socket = false;
        $port = false;
        if (strpos($this->server, ':') !== false) {
            list($server, $port) = explode(':', $this->server);
            if (is_numeric($port) === false) {
                $socket = $port;
                $port = false;
            }
        } elseif (strpos($this->server, '/') !== false) {
            $socket = $this->server;
        }
        if ($socket) {
            $this->link = @new mysqli(null, $this->user, $this->password, $this->database, null, $socket);
        } elseif ($port) {
            $this->link = @new mysqli($server, $this->user, $this->password, $this->database, $port);
        } else {
            $this->link = @new mysqli($this->server, $this->user, $this->password, $this->database);
        }
        if (mysqli_connect_error()) {
            $this->error = 'Link to database cannot be established: ' . mysqli_connect_error();
            return;
        }
        if (!$this->link->query('SET NAMES \'utf8\'')) {
            $this->error = 'No utf-8 support. Please check your server configuration.';
            return;
        }
        return $this->link;
    }

    public function disconnect() {
        @$this->link->close();
    }

    public function query($sql) {
        return $this->link->query($sql);
    }

    public function insert($sql, $params) {
        $result = $this->query($sql);
        if ($result && isset($params['insert_id'])) {
            $result = $this->link->insert_id;
        }
        return $result;
    }

    public function select($sql) {
        $data = array();
        $result = $this->query($sql);
        if (!$result || !is_object($result)) {
            return false;
        }
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }

    public function getMsgError() {
        if (!$this->error) {
            $this->error = $this->link->error;
        }
        return $this->error;
    }

}

class NC_PDO extends NC_Db {

    private function getPDO($host, $user, $password, $dbname, $timeout = 5) {
        $dsn = 'mysql:';
        if ($dbname) {
            $dsn .= 'dbname=' . $dbname . ';';
        }
        if (preg_match('/^(.*):([0-9]+)$/', $host, $matches)) {
            $dsn .= 'host=' . $matches[1] . ';port=' . $matches[2];
        } elseif (preg_match('#^.*:(/.*)$#', $host, $matches)) {
            $dsn .= 'unix_socket=' . $matches[1];
        } else {
            $dsn .= 'host=' . $host;
        }
        $dsn .= ';charset=utf8';

        return new PDO($dsn, $user, $password, array(PDO::ATTR_TIMEOUT => $timeout, PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
    }

    public function connect() {
        try {
            $this->link = $this->getPDO($this->server, $this->user, $this->password, $this->database, 5);
        } catch (PDOException $e) {
            $this->error = 'Cannot connect to PDO database server.';
            return;
        }
        $this->link->exec('SET SESSION sql_mode = \'\'');
        return $this->link;
    }

    public function disconnect() {
        unset($this->link);
    }

    public function query($sql) {
        return $this->link->query($sql);
    }

    public function insert($sql, $params) {
        $result = $this->query($sql);
        if ($result && isset($params['insert_id'])) {
            $result = $this->link->lastInsertId();
        }
        return $result;
    }

    public function select($sql) {
        $result = $this->query($sql);
        if (!$result || !is_object($result)) {
            return false;
        }
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    public function getMsgError() {
        if (!$this->error) {
            $error = $this->link->errorInfo();
            $this->error = ($error[0] == '00000') ? '' : $error[2];
        }
        return $this->error;
    }

}

abstract class NC_Cart {

    public $host = 'localhost';
    public $username = 'root';
    public $password = '';
    public $database = '';
    public $tablePrefix = '';
    public $imageDir = '';
    public $imageDirCategory = '';
    public $imageDirProduct = '';
    public $imageDirManufacturer = '';
    public $version = '';
    public $charset = 'utf8';
    public $cookie_key = '';
    public $extend = '';
    public $check;
    public static $instance = null;

    abstract public function loadConfig();

    public static function instance($check = false) {
        if (is_null(self::$instance)) {
            $class = self::getClass();
            if (!$class) {
                return null;
            } else {
                self::$instance = new $class($check);
            }
        }
        return self::$instance;
    }

    public static function getClass() {
        if (isset($_GET['cart']) && $_GET['cart']) {
            $class = __CLASS__ . '_' . ucfirst($_GET['cart']);
            if (class_exists($class)) {
                return $class;
            } else {
                return null;
            }
        }
        return null;
    }

    public function __construct($check = false) {
        $this->check = $check;
        $this->loadConfig();
    }

    public function getCartVersionFromDb($field, $tableName, $where) {
        $version = '';
        $sql = 'SELECT ' . $field . ' AS version FROM ' . $this->tablePrefix . $tableName . ' WHERE ' . $where;
        $dbConnect = NC_Db::getInstance($this);
        if (!$dbConnect->getError()) {
            $result = $dbConnect->select($sql);
            if ($result) {
                $version = $result[0]['version'];
            }
        }
        return $version;
    }

}

class NC_Cart_Oscommerce extends NC_Cart {

    public function loadConfig() {
        if (file_exists(NC_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'configure.php')) {
            @require_once NC_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'configure.php';
            $this->host = DB_SERVER;
            $this->username = DB_SERVER_USERNAME;
            $this->password = DB_SERVER_PASSWORD;
            $this->database = DB_DATABASE;
            if ($this->check) {
                if (defined('DIR_WS_IMAGES')) {
                    $this->imageDir = DIR_WS_IMAGES;
                } else {
                    $this->imageDir = 'images/';
                }
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
                if (defined('DIR_WS_PRODUCT_IMAGES')) {
                    $this->imageDirProduct = DIR_WS_PRODUCT_IMAGES;
                }
                if (defined('DIR_WS_ORIGINAL_IMAGES')) {
                    $this->imageDirProduct = DIR_WS_ORIGINAL_IMAGES;
                }
            }
        }
    }

}

class NC_Cart_Oscmax extends NC_Cart {

    public function loadConfig() {
        if (file_exists(NC_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'configure.php')) {
            @require_once NC_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'configure.php';
            $this->host = DB_SERVER;
            $this->username = DB_SERVER_USERNAME;
            $this->password = DB_SERVER_PASSWORD;
            $this->database = DB_DATABASE;
            if ($this->check) {
                if (defined('DIR_WS_IMAGES')) {
                    $this->imageDir = DIR_WS_IMAGES;
                } else {
                    $this->imageDir = 'images/';
                }
                $this->imageDirCategory = $this->imageDir . 'categories/';
                $this->imageDirProduct = $this->imageDir . 'products/';
                $this->imageDirManufacturer = $this->imageDir;
                if (defined('DIR_WS_PRODUCT_IMAGES')) {
                    $this->imageDirProduct = DIR_WS_PRODUCT_IMAGES;
                }
                if (defined('DIR_WS_ORIGINAL_IMAGES')) {
                    $this->imageDirProduct = DIR_WS_ORIGINAL_IMAGES;
                }
            }
        }
    }

}

class NC_Cart_Woocommerce extends NC_Cart {

    public function loadConfig() {
        $config = file_get_contents(NC_STORE_BASE_DIR . 'wp-config.php');
        preg_match('/define\s*\(\s*["\']DB_NAME["\'],\s*["\'](.+)["\']\s*\)\s*;/', $config, $match);
        $this->database = $match[1];
        preg_match('/define\s*\(\s*["\']DB_USER["\'],\s*["\'](.+)["\']\s*\)\s*;/', $config, $match);
        $this->username = $match[1];
        preg_match('/define\s*\(\s*["\']DB_PASSWORD["\'],\s*["\'](.*)["\']\s*\)\s*;/', $config, $match);
        $this->password = stripslashes($match[1]);
        preg_match('/define\s*\(\s*["\']DB_HOST["\'],\s*["\'](.+)["\']\s*\)\s*;/', $config, $match);
        $this->host = $match[1];
        if (is_null($this->username) || is_null($this->password) || is_null($this->database)) {
            @require_once( NC_STORE_BASE_DIR . 'wp-load.php' );
            $this->host = DB_HOST;
            $this->username = DB_USER;
            $this->password = DB_PASSWORD;
            $this->database = DB_NAME;
        }
        if ($this->check) {
            preg_match('/define\s*\(\s*["\']DB_CHARSET["\'],\s*["\'](.*)["\']\s*\)\s*;/', $config, $match);
            $this->charset = $match[1];
            preg_match('/\$table_prefix\s*=\s*["\'](.*)["\']\s*;/', $config, $match);
            $this->tablePrefix = $match[1];
            $this->imageDir = 'wp-content/uploads/';
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            $this->version = $this->getCartVersionFromDb('option_value', 'options', "option_name = 'woocommerce_db_version'");
            $active_plugin = $this->getCartVersionFromDb('option_value', 'options', "option_name = 'active_plugins'");
            $active_plugins = $active_plugin ? unserialize($active_plugin) : array();
            if (file_exists(NC_STORE_BASE_DIR . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'polylang' . DIRECTORY_SEPARATOR . 'polylang.php') && in_array('polylang/polylang.php', $active_plugins)) {
                $this->version .= ':pll';
            }
            if (file_exists(NC_STORE_BASE_DIR . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'woocommerce-multilingual' . DIRECTORY_SEPARATOR . 'wpml-woocommerce.php') && in_array('woocommerce-multilingual/wpml-woocommerce.php', $active_plugins)) {
                $this->version .= ':wpml';
            }
        }
    }

}

class NC_Cart_Wordpress extends NC_Cart {

    public function loadConfig() {
        $config = file_get_contents(NC_STORE_BASE_DIR . 'wp-config.php');
        preg_match('/define\s*\(\s*["\']DB_NAME["\'],\s*["\'](.+)["\']\s*\)\s*;/', $config, $match);
        $this->database = $match[1];
        preg_match('/define\s*\(\s*["\']DB_USER["\'],\s*["\'](.+)["\']\s*\)\s*;/', $config, $match);
        $this->username = $match[1];
        preg_match('/define\s*\(\s*["\']DB_PASSWORD["\'],\s*["\'](.*)["\']\s*\)\s*;/', $config, $match);
        $this->password = $match[1];
        preg_match('/define\s*\(\s*["\']DB_HOST["\'],\s*["\'](.+)["\']\s*\)\s*;/', $config, $match);
        $this->host = $match[1];
        if (is_null($this->username) || is_null($this->password) || is_null($this->database)) {
            @require_once( NC_STORE_BASE_DIR . 'wp-load.php' );
            $this->host = DB_HOST;
            $this->username = DB_USER;
            $this->password = DB_PASSWORD;
            $this->database = DB_NAME;
        }
        if ($this->check) {
            preg_match('/define\s*\(\s*["\']DB_CHARSET["\'],\s*["\'](.*)["\']\s*\)\s*;/', $config, $match);
            $this->charset = $match[1];
            preg_match('/\$table_prefix\s*=\s*["\'](.*)["\']\s*;/', $config, $match);
            $this->tablePrefix = $match[1];
            $this->imageDir = 'wp-content/uploads/';
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            $this->version = '';
        }
    }

}

class NC_Cart_Prestashop extends NC_Cart {

    public function loadConfig() {
        /// v1.7
        if (file_exists(NC_STORE_BASE_DIR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'parameters.php')) {
            $parameters = require_once(NC_STORE_BASE_DIR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'parameters.php');
            $this->host = $parameters['parameters']['database_host'];
            if (isset($parameters['parameters']['database_port']) && $parameters['parameters']['database_port']) {
                $this->host .= ':' . $parameters['parameters']['database_port'];
            }
            $this->username = $parameters['parameters']['database_user'];
            $this->database = $parameters['parameters']['database_name'];
            $this->password = $parameters['parameters']['database_password'];
            if ($this->check) {
                $this->tablePrefix = $parameters['parameters']['database_prefix'];
                $this->imageDir = 'img/';
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
                $this->version = '1.7.0.0';
                $this->cookie_key = $parameters['parameters']['cookie_key'];
            }
        } elseif (file_exists(NC_STORE_BASE_DIR . 'config' . DIRECTORY_SEPARATOR . 'settings.inc.php')) {
            require_once(NC_STORE_BASE_DIR . 'config' . DIRECTORY_SEPARATOR . 'settings.inc.php');
            if (defined('_DB_SERVER_')) {
                $this->host = _DB_SERVER_;
            } else {
                $this->host = DB_HOSTNAME;
            }
            if (defined('_DB_USER_')) {
                $this->username = _DB_USER_;
            } else {
                $this->username = DB_USERNAME;
            }
            if (defined('_DB_NAME_')) {
                $this->database = _DB_NAME_;
            } else {
                $this->database = DB_DATABASE;
            }
            $this->password = _DB_PASSWD_;
            if ($this->check) {
                $this->tablePrefix = _DB_PREFIX_;
                $this->imageDir = 'img/';
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
                $this->version = _PS_VERSION_;
                $this->cookie_key = _COOKIE_KEY_;
            }
        }
    }

}

class NC_Cart_Opencart extends NC_Cart {

    public function loadConfig() {
        if (file_exists(NC_STORE_BASE_DIR . 'config.php')) {
            @require_once NC_STORE_BASE_DIR . 'config.php';
            $this->host = DB_HOSTNAME;
            if (defined('DB_PORT') && DB_PORT) {
                $this->host .= ':' . DB_PORT;
            }
            $this->username = DB_USERNAME;
            $this->password = DB_PASSWORD;
            $this->database = DB_DATABASE;
            if ($this->check) {
                $this->tablePrefix = DB_PREFIX;
                $this->imageDir = 'image/';
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
                if (file_exists(NC_STORE_BASE_DIR . 'index.php')) {
                    $index = file_get_contents(NC_STORE_BASE_DIR . 'index.php');
                    preg_match("/define\('\VERSION\'\, \'(.+)\'\)/", $index, $match);
                    $this->version = $match[1];
                }
            }
        } else {
            @require_once NC_STORE_BASE_DIR . 'configuration.php';
            $config = new JConfig();
            $this->host = $config->host;
            $this->username = $config->user;
            $this->password = $config->password;
            $this->database = $config->db;
            if ($this->check) {
                $this->tablePrefix = $config->dbprefix;
                $this->imageDir = 'components/com_mijoshop/opencart/image/';
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
                $config = $base = '';
                if (file_exists(NC_STORE_BASE_DIR . '/components/com_mijoshop/opencart/config.php')) {
                    $config_content = file_get_contents(NC_STORE_BASE_DIR . '/components/com_mijoshop/opencart/config.php');
                }
                if (file_exists(NC_STORE_BASE_DIR . '/components/com_mijoshop/mijoshop/base.php')) {
                    $base = file_get_contents(NC_STORE_BASE_DIR . '/components/com_mijoshop/mijoshop/base.php');
                }
                preg_match("/define\(\"\DB_PREFIX\"\, \'(.+)\'\)/", $config_content, $match);
                $this->tablePrefix .= str_replace("#__", "", $match[1]);

                preg_match('/\$version.+\'(.+)\';/', $base, $match);
                $this->version = $match[1];
            }
        }
    }

}

class NC_Cart_Magento extends NC_Cart {

    public function loadConfig() {
        if (file_exists(NC_STORE_BASE_DIR . 'app/etc/local.xml') || (strpos(NC_STORE_BASE_DIR, '/pub/') && file_exists(NC_STORE_BASE_DIR . '../app/etc/local.xml'))) {
            if (file_exists(NC_STORE_BASE_DIR . 'app/etc/local.xml')) {
                $config = file_get_contents(NC_STORE_BASE_DIR . 'app/etc/local.xml');
            } elseif (strpos(NC_STORE_BASE_DIR, '/pub/')) {
                $config = file_get_contents(NC_STORE_BASE_DIR . '../app/etc/local.xml');
            }
            preg_match("/\<resources\>([\s\S]*)\<\/resources\>/i", $config, $resources);
            $dbconfig = $resources[1];
            preg_match("/\<host\>(.+)\<\/host\>/", $dbconfig, $match);
            $this->host = str_replace(array('<![CDATA[', ']]>'), '', $match[1]);
            preg_match("/\<username\>(.+)\<\/username\>/", $dbconfig, $match);
            $this->username = str_replace(array('<![CDATA[', ']]>'), '', $match[1]);
            preg_match("/\<password\>(.*)\<\/password\>/", $dbconfig, $match);
            $this->password = str_replace(array('<![CDATA[', ']]>'), '', $match[1]);
            preg_match("/\<dbname\>(.+)\<\/dbname\>/", $dbconfig, $match);
            $this->database = str_replace(array('<![CDATA[', ']]>'), '', $match[1]);
            if ($this->check) {
                preg_match("/\<table_prefix\>(.*)\<\/table_prefix\>/", $dbconfig, $match);
                $this->tablePrefix = str_replace(array('<![CDATA[', ']]>'), '', $match[1]);
                $this->version = '1.9.0';
                if (file_exists(NC_STORE_BASE_DIR . 'app/Mage.php')) {
                    $ver = file_get_contents(NC_STORE_BASE_DIR . 'app/Mage.php');
                    if (preg_match("/getVersionInfo[^}]+\'major\' *=> *\'(\d+)\'[^}]+\'minor\' *=> *\'(\d+)\'[^}]+\'revision\' *=> *\'(\d+)\'[^}]+\'patch\' *=> *\'(\d+)\'[^}]+}/s", $ver, $match) == 1) {
                        $mageVersion = $match[1] . '.' . $match[2] . '.' . $match[3] . '.' . $match[4];
                        $this->version = $mageVersion;
                        unset($match);
                    }
                } elseif (file_exists(NC_STORE_BASE_DIR . '../app/Mage.php') && strpos(NC_STORE_BASE_DIR, '/pub/')) {
                    $ver = file_get_contents(NC_STORE_BASE_DIR . '../app/Mage.php');
                    if (preg_match("/getVersionInfo[^}]+\'major\' *=> *\'(\d+)\'[^}]+\'minor\' *=> *\'(\d+)\'[^}]+\'revision\' *=> *\'(\d+)\'[^}]+\'patch\' *=> *\'(\d+)\'[^}]+}/s", $ver, $match) == 1) {
                        $mageVersion = $match[1] . '.' . $match[2] . '.' . $match[3] . '.' . $match[4];
                        $this->version = $mageVersion;
                        unset($match);
                    }
                }
                $this->imageDir = '/media/catalog/';
                $this->imageDirCategory = $this->imageDir . 'category/';
                $this->imageDirProduct = $this->imageDir . 'product/';
                $this->imageDirManufacturer = $this->imageDir;
            }
        } else {
            if (file_exists(NC_STORE_BASE_DIR . 'app/etc/env.php')) {
                $config = require_once(NC_STORE_BASE_DIR . 'app/etc/env.php');
            } elseif (strpos(NC_STORE_BASE_DIR, '/pub/')) {
                $config = require_once(NC_STORE_BASE_DIR . '../app/etc/env.php');
            }
            $this->host = $config['db']['connection']['default']['host'];
            $this->username = $config['db']['connection']['default']['username'];
            $this->password = $config['db']['connection']['default']['password'];
            $this->database = $config['db']['connection']['default']['dbname'];
            if ($this->check) {
                $this->tablePrefix = $config['db']['table_prefix'];
                $this->version = '2.1.0';
                if (file_exists(NC_STORE_BASE_DIR . 'composer.json')) {
                    $ver = file_get_contents(NC_STORE_BASE_DIR . 'composer.json');
                    if (preg_match("/\"version\": \"(.*)\",/", $ver, $match) == 1) {
                        $this->version = $match[1];
                        if (strpos($ver, 'enterprise-edition') !== false) {
                            $this->version .= 'ee';
                        } elseif ($this->isMagentoEE()) {
                            $this->version .= 'ee';
                        }
                        unset($match);
                    }
                } elseif (file_exists(NC_STORE_BASE_DIR . '../composer.json') && strpos(NC_STORE_BASE_DIR, '/pub/')) {
                    $ver = file_get_contents(NC_STORE_BASE_DIR . '../composer.json');
                    if (preg_match("/\"version\": \"(.*)\",/", $ver, $match) == 1) {
                        $this->version = $match[1];
                        if (strpos($ver, 'enterprise-edition') !== false) {
                            $this->version .= 'ee';
                        } elseif ($this->isMagentoEE()) {
                            $this->version .= 'ee';
                        }
                        unset($match);
                    }
                }
                $this->imageDir = '/pub/media/catalog/';
                $this->imageDirCategory = $this->imageDir . 'category/';
                $this->imageDirProduct = $this->imageDir . 'product/';
                $this->imageDirManufacturer = $this->imageDir;
            }
        }
    }

    public function getBaseUrl() {
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $pageURL = "https://";
        } else
            $pageURL = "http://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . dirname($_SERVER["SCRIPT_NAME"]);
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . dirname($_SERVER["SCRIPT_NAME"]);
        }
        return $pageURL;
    }

    public function isMagentoEE() {
        $url = $this->getBaseUrl();
        $url = str_replace('kitconnect', 'magento_version', $url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:86.0) Gecko/20100101 Firefox/86.0';
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        $response = curl_exec($ch);
        curl_close($ch);
        if (strpos($response, 'Enterprise') !== false) {
            return true;
        }
        return false;
    }

}

class NC_Cart_Virtuemart extends NC_Cart {

    public function loadConfig() {
        @require_once NC_STORE_BASE_DIR . 'configuration.php';
        $config = new JConfig();
        $this->host = $config->host;
        $this->username = $config->user;
        $this->password = $config->password;
        $this->database = $config->db;
        $this->tablePrefix = $config->dbprefix;

        $this->imageDir = 'components/com_virtuemart/shop_image/';
        $this->imageDirCategory = $this->imageDir . 'category/';
        $this->imageDirProduct = $this->imageDir . 'product/';
        $this->imageDirManufacturer = $this->imageDir . 'manufacturer/';
        if (is_dir(NC_STORE_BASE_DIR . 'images/stories/virtuemart/product')) {
            $this->imageDir = 'images/stories/virtuemart/';
            $this->imageDirCategory = $this->imageDir . 'category/';
            $this->imageDirProduct = $this->imageDir . 'product/';
            $this->imageDirManufacturer = $this->imageDir . 'manufacturer/';
        }
        if (file_exists(NC_STORE_BASE_DIR . '/administrator/components/com_virtuemart/version.php')) {
            $ver = file_get_contents(NC_STORE_BASE_DIR . '/administrator/components/com_virtuemart/version.php');
            if (preg_match('/\$RELEASE.+\'(.+)\'/', $ver, $match) != 0) {
                $this->version = (string) $match[1];
            }
        }
    }

}

class NC_Cart_Xtcommerce extends NC_Cart {

    public function loadConfig() {
        if (!file_exists(NC_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'configure.org.php')) {
            define('_VALID_CALL', 'TRUE');
            define('_SRV_WEBROOT', 'TRUE');
            @require_once NC_STORE_BASE_DIR . 'conf' . DIRECTORY_SEPARATOR . 'config.php';
            @require_once NC_STORE_BASE_DIR . 'conf' . DIRECTORY_SEPARATOR . 'paths.php';

            $this->username = _SYSTEM_DATABASE_USER;
            $this->password = _SYSTEM_DATABASE_PWD;
            $this->database = _SYSTEM_DATABASE_DATABASE;
            $this->host = _SYSTEM_DATABASE_HOST;
            $this->tablePrefix = DB_PREFIX . '_';
            $this->imageDir = _SRV_WEB_IMAGES;
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            $this->version = '5.0.0';
            $version = $this->getCartVersionFromDb('config_value', 'config', "config_key = '_SYSTEM_VERSION'");
            if ($version != '') {
                $this->version = $version;
            }
        } else {
            @require_once NC_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'configure.php';
            $this->host = DB_SERVER;
            $this->username = DB_SERVER_USERNAME;
            $this->password = DB_SERVER_PASSWORD;
            $this->database = DB_DATABASE;
            if (defined('DIR_WS_IMAGES')) {
                $this->imageDir = DIR_WS_IMAGES;
            } else {
                $this->imageDir = 'images/';
            }
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            if (defined('DIR_WS_PRODUCT_IMAGES')) {
                $this->imageDirProduct = DIR_WS_PRODUCT_IMAGES;
            }
            if (defined('DIR_WS_ORIGINAL_IMAGES')) {
                $this->imageDirProduct = DIR_WS_ORIGINAL_IMAGES;
            }
            $this->version = '3.0.0';
        }
    }

}

class NC_Cart_Hikashop extends NC_Cart {

    public function loadConfig() {
        @require_once NC_STORE_BASE_DIR . 'configuration.php';
        $config = new JConfig();
        $this->host = $config->host;
        $this->username = $config->user;
        $this->password = $config->password;
        $this->database = $config->db;
        $this->tablePrefix = $config->dbprefix;
        if ($this->check) {
            $this->imageDir = 'images/com_hikashop/upload/';
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            $this->version = $this->getCartVersionFromDb('config_value', 'hikashop_config', "config_namekey = 'version'");
        }
    }

}

class NC_Cart_Wpecommerce extends NC_Cart {

    public function loadConfig() {

        $config = file_get_contents(NC_STORE_BASE_DIR . 'wp-config.php');
        preg_match("/define\(\'DB_NAME\', \'(.+)\'\);/", $config, $match);
        $this->database = $match[1];
        preg_match("/define\(\'DB_USER\', \'(.+)\'\);/", $config, $match);
        $this->username = $match[1];
        preg_match("/define\(\'DB_PASSWORD\', \'(.*)\'\);/", $config, $match);
        $this->password = $match[1];
        preg_match("/define\(\'DB_HOST\', \'(.+)\'\);/", $config, $match);
        $this->host = $match[1];
        if (is_null($this->username) || is_null($this->password) || is_null($this->database)) {
            @require_once( NC_STORE_BASE_DIR . 'wp-load.php' );
            $this->host = DB_HOST;
            $this->username = DB_USER;
            $this->password = DB_PASSWORD;
            $this->database = DB_NAME;
        }
        if ($this->check) {
            preg_match("/(table_prefix)(.*)(')(.*)(')(.*)/", $config, $match);
            $this->tablePrefix = $match[4];

            $version = $this->getCartVersionFromDb('option_value', 'options', "option_name = 'wpsc_version'");
            if ($version != '') {
                $this->version = $version;
            } else {
                if (file_exists(NC_STORE_BASE_DIR . 'wp-content' . NC_STORE_BASE_DIR . 'plugins' . NC_STORE_BASE_DIR . 'wp-shopping-cart' . NC_STORE_BASE_DIR . 'wp-shopping-cart.php')) {
                    $conf = file_get_contents(NC_STORE_BASE_DIR . 'wp-content' . NC_STORE_BASE_DIR . 'plugins' . NC_STORE_BASE_DIR . 'wp-shopping-cart' . NC_STORE_BASE_DIR . 'wp-shopping-cart.php');
                    preg_match("/define\('WPSC_VERSION.*/", $conf, $match);
                    if (isset($match[0]) && !empty($match[0])) {
                        preg_match("/\d.*/", $match[0], $project);
                        if (isset($project[0]) && !empty($project[0])) {
                            $version = $project[0];
                            $version = str_replace(array(' ', '-', '_', "'", ');', ')', ';'), '', $version);
                            if ($version != '') {
                                $this->version = strtolower($version);
                            }
                        }
                    }
                }
            }

            if (file_exists(NC_STORE_BASE_DIR . 'wp-content/plugins/shopp/Shopp.php') || file_exists(NC_STORE_BASE_DIR . 'wp-content/plugins/wp-e-commerce/editor.php')) {
                $this->imageDir = 'wp-content/uploads/wpsc/';
                $this->imageDirCategory = $this->imageDir . 'category_images/';
                $this->imageDirProduct = $this->imageDir . 'product_images/';
                $this->imageDirManufacturer = $this->imageDir;
            } elseif (file_exists(NC_STORE_BASE_DIR . 'wp-content/plugins/wp-e-commerce/wp-shopping-cart.php')) {
                $this->imageDir = 'wp-content/uploads/';
                $this->imageDirCategory = $this->imageDir . 'wpsc/category_images/';
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
            } else {
                $this->imageDir = 'images/';
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
            }
        }
    }

}

class NC_Cart_Cscart extends NC_Cart {

    public function loadConfig() {
        $config = file_get_contents(NC_STORE_BASE_DIR . '/config.local.php');
        preg_match("/config\[\'db_host\'\].+\'(.+)\';/", $config, $match);
        $this->host = $match[1];
        preg_match("/config\[\'db_user\'\].+\'(.+)\';/", $config, $match);
        $this->username = $match[1];
        preg_match("/config\[\'db_password\'\].+\'(.*)\';/", $config, $match);
        $this->password = $match[1];
        preg_match("/config\[\'db_name\'\].+\'(.+)\';/", $config, $match);
        $this->database = $match[1];
        if ($this->check) {
            preg_match("/config\[\'table_prefix\'\].+\'(.+)\';/", $config, $match);
            if ($match) {
                $this->tablePrefix = $match[1];
            } else {
                $this->tablePrefix = 'cscart_';
            }
            $this->imageDir = '/images/detailed/';
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            $config_local = file_get_contents(NC_STORE_BASE_DIR . '/config.php');
            preg_match("/define\(\'PRODUCT_VERSION\', \'(.+)\'\);/", $config_local, $match);
            $this->version = $match[1];
            preg_match("/define\(\'PRODUCT_NAME\', \'(.+)\'\);/", $config_local, $match);
            $this->version .= $match[1];
        }
    }

}

class NC_Cart_Xcart extends NC_Cart {

    public function loadConfig() {
        if (file_exists(NC_STORE_BASE_DIR . 'config.php')) {

            $config = file_get_contents(NC_STORE_BASE_DIR . 'config.php');
            preg_match('/\$sql_host.+\'(.+)\';/', $config, $match);
            $this->host = $match[1];
            preg_match('/\$sql_user.+\'(.+)\';/', $config, $match);
            $this->username = $match[1];
            preg_match('/\$sql_db.+\'(.+)\';/', $config, $match);
            $this->database = $match[1];
            preg_match('/\$sql_password.+\'(.*)\';/', $config, $match);
            $this->password = $match[1];
            if ($this->check) {
                $this->tablePrefix = 'xcart_';
                $this->imageDir = 'images/'; // xcart starting from 4.1.x hardcodes images location
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
                $this->version = $this->getCartVersionFromDb('value', 'config', "name = 'version'");
                preg_match('/\$blowfish_key.+\'(.*)\';/', $config, $match);
                $this->cookie_key = $match[1];
            }
        } else {
            $config = file_get_contents(NC_STORE_BASE_DIR . 'top.inc.php');
            @require_once NC_STORE_BASE_DIR . 'top.inc.php';
            $config = XLite::getInstance()->getOptions(array('database_details'));
            $this->host = $config['hostspec'];
            $this->username = $config['username'];
            $this->database = $config['database'];
            $this->password = $config['password'];
            if ($this->check) {
                $this->tablePrefix = $config['table_prefix'];
                $this->imageDir = 'images/'; // xcart v5
                $this->imageDirCategory = $this->imageDir . 'category/';
                $this->imageDirProduct = $this->imageDir . 'product/';
                $this->imageDirManufacturer = $this->imageDir;
                $this->version = $this->getCartVersionFromDb('value', 'config', "name = 'version'");
            }
        }
    }

}

class NC_Cart_Zencart extends NC_Cart {

    public function loadConfig() {
        @require_once NC_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'configure.php';
        $this->username = DB_SERVER_USERNAME;
        $this->password = DB_SERVER_PASSWORD;
        $this->database = DB_DATABASE;
        $this->host = DB_SERVER;
        if ($this->check) {
            $this->tablePrefix = DB_PREFIX;
            $this->imageDir = 'images/';
            if (defined('DIR_WS_IMAGES')) {
                $this->imageDir = DIR_WS_IMAGES;
            }
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            if (defined('DIR_WS_PRODUCT_IMAGES')) {
                $this->imageDirProduct = DIR_WS_PRODUCT_IMAGES;
            }
            if (defined('DIR_WS_ORIGINAL_IMAGES')) {
                $this->imageDirProduct = DIR_WS_ORIGINAL_IMAGES;
            }
            if (file_exists(NC_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'version.php')) {
                @require_once NC_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'version.php';
                $major = PROJECT_VERSION_MAJOR;
                $minor = PROJECT_VERSION_MINOR;
                if (defined('EXPECTED_DATABASE_VERSION_MAJOR') && EXPECTED_DATABASE_VERSION_MAJOR != '') {
                    $major = EXPECTED_DATABASE_VERSION_MAJOR;
                }
                if (defined('EXPECTED_DATABASE_VERSION_MINOR') && EXPECTED_DATABASE_VERSION_MINOR != '') {
                    $minor = EXPECTED_DATABASE_VERSION_MINOR;
                }
                if ($major != '' && $minor != '') {
                    $this->version = $major . '.' . $minor;
                }
            }
            $this->charset = (defined('DB_CHARSET')) ? DB_CHARSET : "";
        }
    }

}

class NC_Cart_Mijoshop extends NC_Cart {

    public function loadConfig() {
        @require_once NC_STORE_BASE_DIR . 'configuration.php';
        $config = new JConfig();
        $this->host = $config->host;
        $this->username = $config->user;
        $this->password = $config->password;
        $this->database = $config->db;
        if ($this->check) {
            $this->tablePrefix = $config->dbprefix;
            $this->imageDir = 'components/com_mijoshop/opencart/image/';
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            $config = $base = '';
            if (file_exists(NC_STORE_BASE_DIR . '/components/com_mijoshop/opencart/config.php')) {
                $config_content = file_get_contents(NC_STORE_BASE_DIR . '/components/com_mijoshop/opencart/config.php');
            }
            if (file_exists(NC_STORE_BASE_DIR . '/components/com_mijoshop/mijoshop/base.php')) {
                $base = file_get_contents(NC_STORE_BASE_DIR . '/components/com_mijoshop/mijoshop/base.php');
            }
            preg_match("/define\(\"\DB_PREFIX\"\, \'(.+)\'\)/", $config_content, $match);
            $this->tablePrefix .= str_replace("#__", "", $match[1]);

            preg_match('/\$version.+\'(.+)\';/', $base, $match);
            $this->version = $match[1];
        }
    }

}

class NC_Cart_Eshop extends NC_Cart {

    public function loadConfig() {
        @require_once NC_STORE_BASE_DIR . 'configuration.php';
        $config = new JConfig();
        $this->host = $config->host;
        $this->username = $config->user;
        $this->password = $config->password;
        $this->database = $config->db;
        if ($this->check) {
            $this->tablePrefix = $config->dbprefix;
            $this->extend = $this->tablePrefix;
            $this->imageDir = '/media/com_eshop/';
            $this->imageDirCategory = $this->imageDir . 'categories';
            $this->imageDirProduct = $this->imageDir . 'products';
            $this->imageDirManufacturer = $this->imageDir . 'manufacturers';
            $config_local = $base = '';
            if (file_exists(NC_STORE_BASE_DIR . '/administrator/components/com_eshop/libraries/defines.php')) {
                $config_local = file_get_contents(NC_STORE_BASE_DIR . '/administrator/components/com_eshop/libraries/defines.php');
                preg_match("/define\(\'ESHOP_TABLE_PREFIX\', \'(.+)\'\);/", $config_local, $match);
                $this->tablePrefix .= $match[1] . '_';
            }
            if (file_exists(NC_STORE_BASE_DIR . '/administrator/components/com_eshop/eshop.xml')) {
                $base = file_get_contents(NC_STORE_BASE_DIR . '/administrator/components/com_eshop/eshop.xml');
                $xml = explode('<version>', $base);
                $xml = explode('</version>', $xml[1]);
                $this->version = $xml[0];
            }
        }
    }

}

class NC_Cart_Jigoshop extends NC_Cart {

    public function loadConfig() {
        $config = file_get_contents(NC_STORE_BASE_DIR . 'wp-config.php');

        preg_match('/define\s*\(\s*\'DB_NAME\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
        $this->database = $match[1];
        preg_match('/define\s*\(\s*\'DB_USER\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
        $this->username = $match[1];
        preg_match('/define\s*\(\s*\'DB_PASSWORD\',\s*\'(.*)\'\s*\)\s*;/', $config, $match);
        $this->password = $match[1];
        preg_match('/define\s*\(\s*\'DB_HOST\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
        $this->host = $match[1];
        if (is_null($this->username) || is_null($this->password) || is_null($this->database)) {
            @require_once( NC_STORE_BASE_DIR . 'wp-load.php' );
            $this->host = DB_HOST;
            $this->username = DB_USER;
            $this->password = DB_PASSWORD;
            $this->database = DB_NAME;
        }
        if ($this->check) {
            preg_match('/define\s*\(\s*\'DB_CHARSET\',\s*\'(.*)\'\s*\)\s*;/', $config, $match);
            $this->charset = $match[1];
            preg_match('/\$table_prefix\s*=\s*\'(.*)\'\s*;/', $config, $match);
            $this->tablePrefix = $match[1];
            $this->imageDir = 'wp-content/uploads/';
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            $jigoshop_cart_id = $this->getCartVersionFromDb('option_value', 'options', "option_name = 'jigoshop_cart_id'");
            $this->version = $jigoshop_cart_id ? '2.0' : '1.0';
        }
    }

}

class NC_Cart_Cubecart extends NC_Cart {

    public function loadConfig() {
        $config = file_get_contents(NC_STORE_BASE_DIR . '/includes/global.inc.php');
        preg_match("/glob\[\'dbhost\'\].+\'(.+)\';/", $config, $match);
        $this->host = $match[1];
        preg_match("/glob\[\'dbusername\'\].+\'(.+)\';/", $config, $match);
        $this->username = $match[1];
        preg_match("/glob\[\'dbpassword\'\].+\'(.*)\';/", $config, $match);
        $this->password = $match[1];
        preg_match("/glob\[\'dbdatabase\'\].+\'(.+)\';/", $config, $match);
        $this->database = $match[1];
        if ($this->check) {
            preg_match("/glob\[\'dbprefix\'\].+\'(.+)\';/", $config, $match);
            if ($match && $match[1]) {
                $this->tablePrefix = $match[1] . 'CubeCart_';
            } else {
                $this->tablePrefix = 'CubeCart_';
            }
            $this->imageDir = '/images/source/';
            if (file_exists(NC_STORE_BASE_DIR . '/ini.inc.php')) {
                $config_local = file_get_contents(NC_STORE_BASE_DIR . '/ini.inc.php');
            } else {
                $config_local = file_get_contents(NC_STORE_BASE_DIR . '/includes/ini.inc.php');
            }
            preg_match("/define\(\'CC_VERSION\', \'(.+)\'\);/", $config_local, $match);
            if ($match) {
                $this->version = $match[1];
            } else {
                preg_match("/ini\[\'ver\'\].+\'(.*)\';/", $config_local, $match);
                $this->version = $match[1];
                $this->imageDir = '/images/uploads/';
            }
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
        }
    }

}

class NC_Cart_Oxideshop extends NC_Cart {

    public function loadConfig() {
        $config = file_get_contents(NC_STORE_BASE_DIR . 'config.inc.php');
        preg_match("/this->dbHost = '(.*)'/", $config, $match);
        $this->host = $match[1];
        preg_match("/this->dbName = '(.*)'/", $config, $match);
        $this->database = $match[1];
        preg_match("/this->dbUser = '(.*)'/", $config, $match);
        $this->username = $match[1];
        preg_match("/this->dbPwd = '(.*)'/", $config, $match);
        $this->password = $match[1];
        if ($this->check) {
            $this->version = $this->getCartVersionFromDb('OXVERSION', 'oxshops', "OXACTIVE = 1");
            if (file_exists(NC_STORE_BASE_DIR . 'bootstrap.php')) {
                @require_once NC_STORE_BASE_DIR . 'bootstrap.php';
                $ox_lang = new oxLang();
                $languages = $ox_lang->getLanguageArray();
                $this->extend = $languages;

                $this->imageDir = 'out/pictures/master/';
                $this->imageDirCategory = $this->imageDir . 'category/thumb';
                $this->imageDirProduct = $this->imageDir . 'product';
                $this->imageDirManufacturer = $this->imageDir . 'manufacturer';
            } else {
                $this->imageDir = 'out/pictures';
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
            }
        }
    }

}

class NC_Cart_Ubercart extends NC_Cart {

    public function loadConfig() {

        @include_once NC_STORE_BASE_DIR . 'sites/default/settings.php';
        if (isset($databases)) {
            $_database = $databases['default']['default'];
            $this->host = $_database['host'];
            if (isset($_database['port']) && $_database['port']) {
                $this->host .= ':' . $_database['port'];
            }
            $this->username = $_database['username'];
            $this->password = $_database['password'];
            $this->database = $_database['database'];
            $this->tablePrefix = $_database['prefix'];
            $this->version = '3.6';
        } else {
            $db_url = str_replace('mysql://', '', $db_url);
            $info = explode('/', $db_url);
            $this->database = $info[1];
            $info2 = explode('@', $info[0]);
            $this->host = $info2[1];
            $info3 = explode(':', $info2[0]);
            $this->username = $info3[0];
            if (isset($info3[1])) {
                $this->password = $info3[1];
            } else {
                $this->password = '';
            }
            $this->tablePrefix = $db_prefix;
            $this->version = '2.13';
        }
        $this->imageDir = '/sites/default/files/';

        $this->imageDirCategory = $this->imageDir;
        $this->imageDirProduct = $this->imageDir;
        $this->imageDirManufacturer = $this->imageDir;
    }

}

class NC_Cart_Abantecart extends NC_Cart {

    public function loadConfig() {
        if (file_exists(NC_STORE_BASE_DIR . 'system' . DIRECTORY_SEPARATOR . 'config.php')) {
            $config = file_get_contents(NC_STORE_BASE_DIR . 'system' . DIRECTORY_SEPARATOR . 'config.php');
            preg_match('/define\s*\(\s*\'DB_DATABASE\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
            $this->database = $match[1];
            preg_match('/define\s*\(\s*\'DB_USERNAME\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
            $this->username = $match[1];
            preg_match('/define\s*\(\s*\'DB_PASSWORD\',\s*\'(.*)\'\s*\)\s*;/', $config, $match);
            $this->password = $match[1];
            preg_match('/define\s*\(\s*\'DB_HOSTNAME\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
            $this->host = $match[1];
            preg_match('/define\s*\(\s*\'DB_PREFIX\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
            $this->tablePrefix = $match[1];
            $this->imageDir = '';
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            $this->version = 0;
        }
    }

}

class NC_Cart_Loadedcommerce extends NC_Cart {

    public function loadConfig() {
        if (file_exists(NC_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'config.php')) {
            @require_once NC_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'config.php';
            // images directory and version
            $this->imageDir = '/images/';
            $this->imageDirCategory = $this->imageDir . 'categories/';
            $this->imageDirProduct = $this->imageDir . 'products/originals/';
            $this->imageDirManufacturer = $this->imageDir . 'manufacturers/';
            $this->version = '7.0.0';
        } else {
            @require_once NC_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'configure.php';
            // images directory and version
            $this->imageDir = '/images/';
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            $this->version = '6.5.3';
        }
        if (defined('DB_SERVER')) {
            $this->host = DB_SERVER;
        } else {
            $this->host = DB_HOSTNAME;
        }
        if (defined('DB_SERVER_USERNAME')) {
            $this->username = DB_SERVER_USERNAME;
        } else {
            $this->username = DB_USERNAME;
        }
        $this->password = DB_SERVER_PASSWORD;
        $this->database = DB_DATABASE;
        if (defined('DB_TABLE_PREFIX')) {
            $this->tablePrefix = DB_TABLE_PREFIX;
        } else {
            $this->tablePrefix = '';
        }
    }

}

class NC_Cart_Shopp extends NC_Cart {

    public function loadConfig() {
        $config = file_get_contents(NC_STORE_BASE_DIR . 'wp-config.php');

        preg_match('/define\s*\(\s*\'DB_NAME\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
        $this->database = $match[1];
        preg_match('/define\s*\(\s*\'DB_USER\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
        $this->username = $match[1];
        preg_match('/define\s*\(\s*\'DB_PASSWORD\',\s*\'(.*)\'\s*\)\s*;/', $config, $match);
        $this->password = $match[1];
        preg_match('/define\s*\(\s*\'DB_HOST\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
        $this->host = $match[1];
        if (is_null($this->username) || is_null($this->password) || is_null($this->database)) {
            @require_once( NC_STORE_BASE_DIR . 'wp-load.php' );
            $this->host = DB_HOST;
            $this->username = DB_USER;
            $this->password = DB_PASSWORD;
            $this->database = DB_NAME;
        }
        if ($this->check) {
            preg_match('/define\s*\(\s*\'DB_CHARSET\',\s*\'(.*)\'\s*\)\s*;/', $config, $match);
            $this->charset = $match[1];
            preg_match('/\$table_prefix\s*=\s*\'(.*)\'\s*;/', $config, $match);
            $this->tablePrefix = $match[1];
            $this->imageDir = '';
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            $version = $this->getCartVersionFromDb('value', 'shopp_meta', "name = 'version'");
            $this->version = $version;
        }
    }

}

class NC_Cart_Litecart extends NC_Cart {

    public function loadConfig() {
        if (file_exists(NC_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'config.inc.php')) {
            @require_once NC_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'config.inc.php';
            $this->host = DB_SERVER;
            $this->username = DB_USERNAME;
            $this->password = DB_PASSWORD;
            $this->database = DB_DATABASE;
            $this->tablePrefix = DB_TABLE_PREFIX;
            if ($this->check) {
                $this->imageDir = 'images/';
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
            }
        }
    }

}

class NC_Cart_Gambio extends NC_Cart {

    public function loadConfig() {
        if (file_exists(NC_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'configure.php')) {
            @require_once NC_STORE_BASE_DIR . 'includes' . DIRECTORY_SEPARATOR . 'configure.php';
            $this->host = DB_SERVER;
            $this->username = DB_SERVER_USERNAME;
            $this->password = DB_SERVER_PASSWORD;
            $this->database = DB_DATABASE;
            if ($this->check) {
                if (defined('DIR_WS_IMAGES')) {
                    $this->imageDir = DIR_WS_IMAGES;
                } else {
                    $this->imageDir = 'images/';
                }
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
                if (defined('DIR_WS_PRODUCT_IMAGES')) {
                    $this->imageDirProduct = DIR_WS_PRODUCT_IMAGES;
                }
                if (defined('DIR_WS_ORIGINAL_IMAGES')) {
                    $this->imageDirProduct = DIR_WS_ORIGINAL_IMAGES;
                }
            }
        }
    }

}

class NC_Cart_Easydigitaldownloads extends NC_Cart {

    public function loadConfig() {
        $config = file_get_contents(NC_STORE_BASE_DIR . 'wp-config.php');

        preg_match('/define\s*\(\s*\'DB_NAME\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
        $this->database = $match[1];
        preg_match('/define\s*\(\s*\'DB_USER\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
        $this->username = $match[1];
        preg_match('/define\s*\(\s*\'DB_PASSWORD\',\s*\'(.*)\'\s*\)\s*;/', $config, $match);
        $this->password = $match[1];
        preg_match('/define\s*\(\s*\'DB_HOST\',\s*\'(.+)\'\s*\)\s*;/', $config, $match);
        $this->host = $match[1];
        if (is_null($this->username) || is_null($this->password) || is_null($this->database)) {
            @require_once( NC_STORE_BASE_DIR . 'wp-load.php' );
            $this->host = DB_HOST;
            $this->username = DB_USER;
            $this->password = DB_PASSWORD;
            $this->database = DB_NAME;
        }
        if ($this->check) {
            preg_match('/define\s*\(\s*\'DB_CHARSET\',\s*\'(.*)\'\s*\)\s*;/', $config, $match);
            $this->charset = $match[1];
            preg_match('/\$table_prefix\s*=\s*\'(.*)\'\s*;/', $config, $match);
            $this->tablePrefix = $match[1];
            $this->imageDir = '/wp-content/uploads/edd/';
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            $version = $this->getCartVersionFromDb('option_value', 'options', "option_name = 'edd_version'");
            $this->version = $version;
        }
    }

}

class NC_Cart_Joomla extends NC_Cart {

    public function loadConfig() {
        @require_once NC_STORE_BASE_DIR . 'configuration.php';
        $config = new JConfig();
        $this->host = $config->host;
        $this->username = $config->user;
        $this->password = $config->password;
        $this->database = $config->db;
        $this->tablePrefix = $config->dbprefix;

        $this->imageDir = '/images/';
        $this->imageDirCategory = $this->imageDir . '';
        $this->imageDirProduct = $this->imageDir . '';
        $this->imageDirManufacturer = $this->imageDir . '';
        $this->version = '';
    }

}

class NC_Cart_Shopscript extends NC_Cart {

    public function loadConfig() {
        $db = require_once NC_STORE_BASE_DIR . '/wa-config/db.php';
        $this->host = $db['default']['host'];
        $this->username = $db['default']['user'];
        $this->password = $db['default']['password'];
        $this->database = $db['default']['database'];
        $this->tablePrefix = '';

        $this->imageDir = '/wa-data/public/shop/products/';
        $this->imageDirCategory = $this->imageDir . '';
        $this->imageDirProduct = $this->imageDir . '';
        $this->imageDirManufacturer = $this->imageDir . '';
        $this->version = '';
    }

}

class NC_Cart_Drupal extends NC_Cart {

    public function loadConfig() {

        @include_once(NC_STORE_BASE_DIR . '/sites/default/settings.php');
        $default = $databases['default']['default'];
        $this->database = $default['database'];
        $this->username = $default['username'];
        $this->password = $default['password'];
        $this->host = $default['host'];
        if ($this->check) {
            $config = @file_get_contents(NC_STORE_BASE_DIR . '/includes/bootstrap.inc');
            preg_match('/define\s*\(\s*\'VERSION\',\s*\'(.*)\'\s*\)\s*;/', $config, $match);
            $this->version = $match[1];
            $this->charset = '';
            $this->tablePrefix = $default['prefix'];
            $this->imageDir = '/sites/default/files/';
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
        }
    }

}

class NC_Cart_Interspire extends NC_Cart {

    public function loadConfig() {
        if (file_exists(NC_STORE_BASE_DIR . 'config' . DIRECTORY_SEPARATOR . 'config.php')) {
            @require_once NC_STORE_BASE_DIR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
            $this->host = $GLOBALS['ISC_CFG']["dbServer"];
            $this->username = $GLOBALS['ISC_CFG']["dbUser"];
            $this->password = $GLOBALS['ISC_CFG']["dbPass"];
            $this->database = $GLOBALS['ISC_CFG']["dbDatabase"];
            $this->tablePrefix = $GLOBALS['ISC_CFG']["tablePrefix"];
            if ($this->check) {
                $this->imageDir = '/product_images/';
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
            }
        }
    }

}

class NC_Cart_Kabiacommerce extends NC_Cart {

    public function loadConfig() {
        if (file_exists(NC_STORE_BASE_DIR . 'configs/config.php')) {
            include_once NC_STORE_BASE_DIR . 'configs/config.php';
            $db = $config['db'];
            $this->host = $db['host'];
            $this->username = $db['username'];
            $this->password = $db['password'];
            $this->database = $db['dbname'];
            $this->tablePrefix = $db['prefix'];
            if ($this->check) {
                $this->imageDir = '';
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
            }
        }
        $public = str_replace('/public', '', NC_STORE_BASE_DIR);
        if (file_exists($public . 'configs/config.php')) {
            include_once $public . 'configs/config.php';
            $db = $config['db'];
            $this->host = $db['host'];
            $this->username = $db['username'];
            $this->password = $db['password'];
            $this->database = $db['dbname'];
            $this->tablePrefix = $db['prefix'];
            if ($this->check) {
                $this->imageDir = '';
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
            }
        }
    }

}

class NC_Cart_Shopware extends NC_Cart {

    public function loadConfig() {
        if (file_exists(NC_STORE_BASE_DIR . 'config.php')) {
            $config = require_once NC_STORE_BASE_DIR . 'config.php';
            $db = $config['db'];
            $this->host = $db['host'];
            if ($db['port']) {
                $this->host .= ':' . $db['port'];
            }
            $this->username = $db['username'];
            $this->password = $db['password'];
            $this->database = $db['dbname'];
            $this->tablePrefix = '';
            if ($this->check) {
                if ($application = @file_get_contents(NC_STORE_BASE_DIR . 'engine/Shopware/Application.php')) {
                    preg_match('/const VERSION\s*=\s*["\'](.+)["\']\s*;/', $application, $matches);
                    $version = isset($matches[1]) ? $matches[1] : false;
                    if ($version) {
                        $this->version = $version;
                    }
                }
                $this->imageDir = 'media/image/';
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
            }
        } elseif (file_exists(NC_STORE_BASE_DIR . '.env') || file_exists(NC_STORE_BASE_DIR . '../.env')) {
            if (file_exists(NC_STORE_BASE_DIR . '.env')) {
                $config = @file_get_contents(NC_STORE_BASE_DIR . '.env');
                $img_dir = 'public';
            } else {
                $config = @file_get_contents(NC_STORE_BASE_DIR . '../.env');
            }
            preg_match('/DATABASE_URL\=mysql\:\/\/.*?\n/', $config, $dbconfig);
            $db_query = str_replace('DATABASE_URL=mysql://', '', trim($dbconfig[0]));

            preg_match("/\/(.*)$/", $db_query, $matches);
            $this->database = $matches[1];
            preg_match("/^(.*?)\:/", $db_query, $matches);
            $this->username = $matches[1];
            preg_match("/\:(.*?)\@/", $db_query, $matches);
            $this->password = urldecode((string)@$matches[1]);
            preg_match("/\@(.*?)\:(.*?)\//", $db_query, $matches);
            $this->host = $matches[1];
            if (isset($matches[2])) {
                $this->host .= ':' . $matches[2];
            }
            if($this->check){
                $this->imageDir = $img_dir . '/media/';
                $this->imageDirCategory = $this->imageDir;
                $this->imageDirProduct = $this->imageDir;
                $this->imageDirManufacturer = $this->imageDir;
                $this->version = '6.0';
            }
        }
    }

}

class NC_Cart_Joocart extends NC_Cart {

    public function loadConfig() {
        @require_once NC_STORE_BASE_DIR . 'configuration.php';
        $config = new JConfig();
        $this->host = $config->host;
        $this->username = $config->user;
        $this->password = $config->password;
        $this->database = $config->db;
        if ($this->check) {
            $this->tablePrefix = $config->dbprefix;
            $this->imageDir = '/components/com_opencart/image';
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
            $config = $base = '';
            if (file_exists(NC_STORE_BASE_DIR . '/components/com_opencart/config.php')) {
                $config = file_get_contents(NC_STORE_BASE_DIR . '/components/com_opencart/config.php');
                preg_match('/define\s*\(\s*["\']DB_PREFIX["\'],\s*["\'](.+)["\']\s*\)\s*;/', $config, $match);
                $this->tablePrefix = $match[1];
            }
            if (file_exists(NC_STORE_BASE_DIR . '/components/com_opencart/index.php')) {
                $config = file_get_contents(NC_STORE_BASE_DIR . '/components/com_opencart/index.php');
                preg_match('/define\s*\(\s*["\']VERSION["\'],\s*["\'](.+)["\']\s*\)\s*;/', $config, $match);
                $this->version = $match[1];
            }
        }
    }

}

class NC_Cart_J2store extends NC_Cart {

    public function loadConfig() {
        @require_once NC_STORE_BASE_DIR . 'configuration.php';
        $config = new JConfig();
        $this->host = $config->host;
        $this->username = $config->user;
        $this->password = $config->password;
        $this->database = $config->db;
        $this->tablePrefix = $config->dbprefix;
        //components/com_j2store
        $this->imageDir = 'images';
        $this->imageDirCategory = $this->imageDir;
        $this->imageDirProduct = $this->imageDir;
        $this->imageDirManufacturer = $this->imageDir;
    }

}

class NC_Cart_Sylius extends NC_Cart {

    public function loadConfig() {
        $config = file_get_contents(dirname(NC_STORE_BASE_DIR) . '/app/config/parameters.yml');

        preg_match('/env\s*\(SYLIUS_DATABASE_NAME\s*\):\s*(.+)/', $config, $match);
        $this->database = $match[1];
        preg_match('/env\s*\(SYLIUS_DATABASE_USER\s*\):\s*(.+)/', $config, $match);
        $this->username = $match[1];
        preg_match('/env\s*\(SYLIUS_DATABASE_PASSWORD\s*\):\s*(.+)/', $config, $match);
        $this->password = $match[1];
        preg_match('/env\s*\(SYLIUS_DATABASE_HOST\s*\):\s*(.+)/', $config, $match);
        $this->host = $match[1];
        $this->tablePrefix = '';
        if ($this->check) {
            $this->imageDir = '/media/image';
            $this->imageDirCategory = $this->imageDir;
            $this->imageDirProduct = $this->imageDir;
            $this->imageDirManufacturer = $this->imageDir;
        }
    }

}

class NC_Cart_Bagisto extends NC_Cart {

    public function loadConfig() {
        $lines = file(NC_STORE_BASE_DIR . '../.env');
        $host = $username = $password = $database = $tablePrefix = '';
        foreach ($lines as $line) {
            $line_data = explode('=', $line);
            if ($line_data[0] == 'DB_HOST') {
                $host = $line_data[1];
            }
            if ($line_data[0] == 'DB_USERNAME') {
                $username = $line_data[1];
            }
            if ($line_data[0] == 'DB_PASSWORD') {
                $password = $line_data[1];
            }
            if ($line_data[0] == 'DB_DATABASE') {
                $database = $line_data[1];
            }
            if ($line_data[0] == 'DB_PREFIX') {
                $tablePrefix = $line_data[1];
            }
        }
        $this->host = trim($host);
        $this->username = trim($username);
        $this->password = trim($password);
        $this->database = trim($database);
        if ($this->check) {
            $this->tablePrefix = trim($tablePrefix);
            $this->version = '';
            $this->imageDir = '/storage/';
            $this->imageDirCategory = $this->imageDir . '';
            $this->imageDirProduct = $this->imageDir . '';
            $this->imageDirManufacturer = $this->imageDir;
        }
    }

}

class NC_Action_Clearcache extends NC_Action {

    public function run() {
        $data = array();
        if (isset($_REQUEST['clearcaches'])) {
            $clearcaches = unserialize(base64_decode($_REQUEST['clearcaches']));
            foreach ($clearcaches as $key => $clear_cache) {
                $data = $this->processClearCache($clear_cache['type']);
            }
            if ($data) {
                NC_Response::success('', $data);
            } else {
                NC_Response::error('Cannot reindex the Target Store.');
            }
        } else {
            NC_Response::success('');
        }
        return;
    }

    public function processClearCache($type) {
        $func = strtolower($type) . 'ClearCache';
        $result = $this->$func();
        return $result;
    }

    public function magentoClearCache() {
        chdir('../');
        $phpExecutable = $this->getPHPExecutable();
        if ($phpExecutable) {
            $memoryLimit = '-d memory_limit=1024M';
            if (file_exists(NC_STORE_BASE_DIR . 'app/etc/env.php')) {

                $indexer = "nohup $phpExecutable $memoryLimit bin/magento indexer:reindex;";
                $imagesResize = "nohup $phpExecutable $memoryLimit bin/magento catalog:images:resize;";
                $clearCache = "nohup $phpExecutable $memoryLimit bin/magento cache:flush;";
                $rmCache = "nohup rm -rf var/cache;";
                @exec("($indexer $imagesResize $clearCache $rmCache) &>/dev/null &");
            } else {
                @exec("nohup $phpExecutable shell/indexer.php --reindexall > /dev/null 2>/dev/null & echo $!");
            }
        } else {
            return false;
        }
        return true;
    }

    public function cscartClearCache() {
        $dir = NC_STORE_BASE_DIR . 'var/cache/';
        $res = $this->_removeDirRec($dir, false);

        if ($res) {
            return "OK";
        } else {
            return "ERROR";
        }
    }

    ########

    protected function _removeDirRec($dir, $removeDir = true, $fileExclude = '') {
        if (!@file_exists($dir)) {
            return true;
        }

        $result = true;
        if ($objs = glob($dir . '/*')) {
            foreach ($objs as $obj) {
                if ((trim($fileExclude) != '') && strpos($obj, $fileExclude) !== false) {
                    continue;
                }
                if (is_dir($obj)) {
                    $this->_removeDirRec($obj, true, $fileExclude);
                } else {
                    if (!@unlink($obj)) {
                        $result = false;
                    }
                }
            }
        }

        if ($removeDir && !@rmdir($dir)) {
            $result = false;
        }

        return $result;
    }

    protected function getPHPExecutable() {
        $paths = explode(PATH_SEPARATOR, getenv('PATH'));
        $paths[] = PHP_BINDIR;
        foreach ($paths as $path) {
            if (isset($_SERVER["WINDIR"]) && strstr($path, 'php.exe') && @file_exists($path) && is_file($path)) {
                return $path;
            } else {
                $phpPath = $path . DIRECTORY_SEPARATOR . "php" . (isset($_SERVER["WINDIR"]) ? ".exe" : "");
                if (@file_exists($phpPath) && is_file($phpPath)) {
                    return $phpPath;
                }
            }
        }
        return false;
    }

}

define('NC_TOKEN', 'x0xpm7m254zuvyt8lttji3ker3t36kotkc9x0lo4eeo148yerh');
define('NC_STORE_BASE_DIR', realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR);
$sweety = new NC_Kitconnect();
$sweety->run();
