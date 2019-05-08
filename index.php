<?php
//必填参数研发环境
// $schema = "eduwork";
// $table_name = "bc_app_int_merchant_parent_records";
// $author = "xuxiongzi";
// $port = "3306";
// $prefix = strstr($schema,"_",true);
// $servername = "192.168.1.177";
// $username = "xuxiongzi";
// $password = "zSGzzHMX7p3fJt7AVKCzP9yRwLIIEsFW";

// 未来校长之家
$table_name = "bc_app_articles";

$schema = "eduwork";
$author = "xuxiongzi";
$port = "3306";
$prefix = strstr($schema,"_",true);
$servername = "192.168.1.177";
$username = "xuxiongzi";
$password = "zSGzzHMX7p3fJt7AVKCzP9yRwLIIEsFW";

//创建连接
$conn = new mysqli($servername,$username,$password,$schema,$port);

//检查连接
if($conn->connect_error) {
    die("连接失败".$conn->connect_error);
}
echo "连接成功<br>";
$sql = "SELECT COLUMN_NAME,COLUMN_COMMENT FROM information_schema.columns WHERE table_name='{$table_name}'
";
$select_table = "
SELECT table_comment,TABLE_NAME FROM information_schema.`TABLES`WHERE table_name='{$table_name}' AND TABLE_SCHEMA='{$schema}'
";
$table_info = $conn->query($select_table);
$table = $table_info->fetch_array();
//echo "<pre>";
//print_r($table);die;

$table_comment = $table[0];
$baseName = str_replace("_"," ",$table[1]);
$baseName = ucwords($baseName);
$baseName = str_replace(" ","",$baseName);
//model 名称
$class_name = $baseName."Model";
// controller名称
$controller_name = $baseName."Controller";
//logic名称
$logic_name = $baseName."Logic";


$column_infos = $conn->query($sql);
if ($column_infos->num_rows > 0) {
    //创建文件
    $myfile = fopen("source/".$class_name.".php","w");

    // 输出数据
    while($row = $column_infos->fetch_assoc()) {
        $key = $row['COLUMN_NAME'];
        $param[$key] = $row['COLUMN_COMMENT'];
        $str = "COLUMN_COMMENT: " . $row["COLUMN_COMMENT"]. " - Name: " . $row["COLUMN_NAME"]. " " . $row["COLUMN_NAME"]. "<br>";
    }
    //echo "<pre>";
    //print_r($param);
###############################构建model开始#############################################################
   $tmpstr = "<?php

/**
 * 描述：".$table_comment.
 "
 *
 * @copyright Copyright 2012-2019, BAONAHAO Software Foundation, Inc. ( http://api.baonahao.com/ )
 * @link http://api.baonahao.com api(tm) Project
 * @date ".date('Y-m-d H:i:s')."
 * @author $author <$author@xiaohe.com>
 */
class ".$class_name." extends BaseModel
{
    public function initialize()
    {
        $"."this->setSource('".$table_name."');
        $"."this->setReadConnectionService('".$prefix."_slave');
        $"."this->setWriteConnectionService('".$prefix."_master');
    }
    /**
     * 描述：获取".$table_comment."列表
     * @param
     * @return array
     * @author xuxiongzi <xuxiongzi@xiaohe.com>
     */
    ";

$tmpstr .= "public function queryPage($"."data)
    ";
$tmpstr .= "{
        ";
$tmpstr .= "//参数
";
foreach ($param as $k=>$v) {$tmpstr .= "        $".$k. " = getArrVal($"."data,"."'" .$k."'); // ".$v."
";
}
$tmpstr .="
        $"."order = getArrVal($"."data, 'order', 'a.modified DESC');//排序值
        $"."curr_page = getArrVal($"."data, 'curr_page', 1);
        $"."page_size = getArrVal($"."data, 'page_size', 10);
        $"."offset = ($"."curr_page - 1) * $"."page_size;
        $"."offset = empty($"."offset) ? '' : \" offset \" . $"."offset;
        $"."limit = \" limit \" . $"."page_size;

        //所查字段
        $"."field = \"
            ";
$end = end($param);
$endKey = key($param);
foreach ($param as $k=>$v) {
    $tmpstr .= "a.".$k;
    if($k != $endKey){
        $tmpstr .=",";
    }
    $tmpstr .="
            ";
}
$tmpstr .="\";";
$tmpstr .= "
        $"."from = \" from {\$this->getSource()} a \";
        ";
$tmpstr .= "
        $"."where = \" where true \";
        ";
foreach($param as $k=>$v) {
    $tmpstr .= "
        if ($".$k.") {
            $"."where .= \" AND a.".$k." = '{\$".$k."}' \";
        }
    ";
}
$tmpstr .= "
        if (\$order) {
            \$order = \" ORDER BY \$order \";
        }

        \$result = \$this->findPage(\$field, \$from, \$where, \$order, \$limit, \$offset, \$page_size);
        return \$result;
    }
";
$add_param = $param;
    unset($add_param['id']);
    unset($add_param['creator_id']);
    unset($add_param['created']);
    unset($add_param['modifier_id']);
    unset($add_param['modified']);

$tmpstr .= "
    /**
     * 描述：add
     * @param
     * @return string
     * @author $author <$author@xiaohe.com>
     * @throws
     */
    public function addModel(\$data){

        // 参数
        \$param['id']                = getUuid();
        ";
foreach ($add_param as $k=>$v) {
    $tmpstr .= "
        if(isset(\$data['$k'])) \$param['$k'] = getArrVal(\$data,'$k');
    ";

}
$tmpstr .= "
        if(isset(\$data['operator_id'])) \$param['creator_id'] = \$param['modifier_id'] = getArrVal(\$data,\"operator_id\");
        \$param['created'] = \$param['modified'] = date('Y-m-d H:i:s');

        \$result = \$this->insert(\$param);
        if(\$result) return \$param['id'];
        return \$result;
    }

";

    $update_param = $param;
    unset($add_param['creator_id']);
    unset($add_param['created']);
    unset($add_param['modifier_id']);
    unset($add_param['modified']);
$tmpstr .= "
    /**
     * 描述：update
     * @param
     * @return boolean
     * @author $author <$author@xiaohe.com>
     * @throws
     */
    public function updateModel(\$data) {

        // return value
        \$result = false;

        \$id = getArrVal(\$data,'id');
        if(empty(\$id)) return \$result;
        \$param['id'] = \$id; ";
    foreach ($update_param as $k=>$v) {
        $tmpstr .= "
        if(isset(\$data['$k'])) \$param['$k'] = getArrVal(\$data,'$k');
    ";

    }

$tmpstr .="
        if(isset(\$data['operator_id'])) \$param['modifier_id'] = getArrVal(\$data,\"operator_id\");
        \$result = \$this->updateById(\$param);
        return \$result;
    }
}
";

    fwrite($myfile,$tmpstr);
#############################构建model结束构建控制器开始######################################################

    $controllerFile = fopen("source/".$controller_name.".php","w");
    $controllerStr = "<?php

/**
 * 描述：$table_comment 控制器
 *
 * @copyright Copyright 2012-2018, BAONAHAO Software Foundation, Inc. ( http://api.baonahao.com/ )
 * @link http://api.baonahao.com api(tm) Project
 * @date ".date('Y-m-d H:i:s')."
 * @author $author <$author@xiaohe.com>
 */
class $controller_name extends AppController
{
    /**
     * 描述：add $table_comment
     * @param
        | 字段 | 类型 | 是否必填 | 说明 |
        | :-- | :-- | :-- | :-- |
        | `data[operator_id]` | `String` | 是 | 操作人ID | ";
    foreach ($param as $k => $value) {
        $controllerStr .="
        | `data[$k]` | `String` | 否 | $value | ";
    //  * @param 非必填 string data[$k]  $value ";

    }
$controllerStr .="
     * @return string
     * @date ".date('Y-m-d H:i:s')."
     * @author xuxiongzi <xuxiongzi@xiaohe.com>
     */
    public function add$baseName() {
        //获取参数中的data参数
        \$data = getArrVal(\$this->"."args,'data');

        //获取参数中keys参数
        \$keys = getArrVal(\$this->args,'keys');

        //验证项
        \$rules = array(";
    foreach ($param as $k => $value) {
        $controllerStr .="
            '$k' => array(
                array('method'=>'isset', 'msg'=>'$k'),
                array('method'=>'empty', 'msg'=>'$k'),
            ),";

    }
    $controllerStr .="
        );

        Validate::data(\$data, \$rules, \$keys); //业务数据验证

        /**
         * 业务逻辑实现
         */
        \$logic = new $logic_name();

        \$result = \$logic->add$baseName(\$data);

        dataReturn(true,'API_COMM_001',\$result);
    }

    /**
     * 描述：更新$table_comment
     * @param
        | 字段 | 类型 | 是否必填 | 说明 |
        | :-- | :-- | :-- | :-- |
        | `data[operator_id]` | `String` | 是 | 操作人ID | ";
 foreach ($param as $k => $value) {
     $controllerStr .="
        | `data[$k]` | `String` | 否 | $value | ";
 }
$controllerStr .="
     * @return string
     * @date ".date('Y-m-d H:i:s')."
     * @author xuxiongzi <xuxiongzi@xiaohe.com>
     */
    public function update$baseName() {
        //获取参数中的data参数
        \$data = getArrVal(\$this->args,'data');

        //获取参数中keys参数
        \$keys = getArrVal(\$this->args,'keys');

        //验证项
        \$rules = array(";
    foreach ($param as $k => $value) {
        $controllerStr .="
            '$k' => array(
                array('method'=>'isset', 'msg'=>'$k'),
                array('method'=>'empty', 'msg'=>'$k'),
            ),";

    }
    $controllerStr .="
        );

        Validate::data(\$data, \$rules, \$keys); //业务数据验证

        /**
         * 业务逻辑实现
         */
        \$logic = new $logic_name();

        \$result = \$logic->update$baseName(\$data);

        dataReturn(true,'API_COMM_001',\$result);
    }

    /**
     * 描述：获取$table_comment 列表
     * @param
        | 字段 | 类型 | 是否必填 | 说明 |
        | :-- | :-- | :-- | :-- |
        | `data[operator_id]` | `String` | 是 | 操作人ID | ";
 foreach ($param as $k => $value) {
     $controllerStr .="
        | `data[$k]` | `String` | 否 | $value | ";
 }
$controllerStr .="
     * @date ".date('Y-m-d H:i:s')."
     * @return string
     * @author $author <$author@xiaohe.com>
     */
    public function get".$baseName."List() {
        //获取参数中的data参数
        \$data = getArrVal(\$this->args,'data');

        //获取参数中keys参数
        \$keys = getArrVal(\$this->args,'keys');

        //验证项
        \$rules = array(";
    foreach ($param as $k => $value) {
        $controllerStr .="
            '$k' => array(
                array('method'=>'isset', 'msg'=>'$k'),
                array('method'=>'empty', 'msg'=>'$k'),
            ),";

    }
    $controllerStr .="
        );

        Validate::data(\$data, \$rules, \$keys); //业务数据验证

        /**
         * 业务逻辑实现
         */
        \$logic = new $logic_name();

        \$result = \$logic->get".$baseName."List(\$data);

        dataReturn(true,'API_COMM_001',\$result);
    }

}";

    fwrite($controllerFile,$controllerStr);
##################创建Controller结束
    $logicFile = fopen("source/".$logic_name.".php","w");
    $logicStr = "<?php

/**
 * 描述：$table_comment 业务层
 *
 * @copyright Copyright 2012-2018, BAONAHAO Software Foundation, Inc. ( http://api.baonahao.com/ )
 * @link http://api.baonahao.com api(tm) Project
 * @date ".date('Y-m-d H:i:s')."
 * @author $author <$author@xiaohe.com>
 */
class ".$logic_name." extends \\Phalcon\\Mvc\\Controller
{
    /**
     * 描述：add $table_comment
     * @param
     * @return boolean
     * @author xuxiongzi <xuxiongzi@xiaohe.com>
     */
    public function add$baseName(\$data) {
        \$model = new ".$baseName."Model();
        return \$model->addModel(\$data);
    }

    /**
     * 描述：update $table_comment
     * @param
     * @return boolean
     * @author xuxiongzi <xuxiongzi@xiaohe.com>
     */
    public function update$baseName(\$data) {
        \$model = new ".$baseName."Model();
        return \$model->updateModel(\$data);
    }

    /**
     * 描述：获取
     * @param
     * @return array
     * @author $author <$author@xiaohe.com>
     */
    public function get".$baseName."List(\$data) {
        \$model = new ".$baseName."Model();

        \$result = \$model->queryPage(\$data);
        if(\$result['total'] == '0') return \$result;

        return \$result;
    }

}
    ";
    fwrite($logicFile,$logicStr);
} else {
    echo "0 结果";
}

$conn->close();
echo"创建完成";
?>




