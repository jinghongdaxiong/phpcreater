<?php
/**
 * 数据库连接
 */
$schema = "eduwork"; // 库名
$table_name = "bc_app_articles"; //表名
$author = "xuxiongzi"; // 用户名
$port = "3306";
$prefix = strstr($schema, "_", true);
$servername = "192.168.1.177";
$username = "xuxiongzi";
$password = "zSGzzHMX7p3fJt7AVKCzP9yRwLIIEsFW";

//创建连接
$conn = new mysqli($servername, $username, $password, $schema, $port);

//检查连接
if ($conn->connect_error) {
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
$baseName = str_replace("_", " ", $table[1]);
$baseName = ucwords($baseName);
$baseName = str_replace(" ", "", $baseName);


$column_infos = $conn->query($sql);
if ($column_infos->num_rows > 0) {
    //创建文件
    $myfile = fopen("source/comment.md", "w");

    // 输出数据
    while ($row = $column_infos->fetch_assoc()) {
        $key = $row['COLUMN_NAME'];
        $param[$key] = $row['COLUMN_COMMENT'];
        $str = "COLUMN_COMMENT: " . $row["COLUMN_COMMENT"]. " - Name: " . $row["COLUMN_NAME"]. " " . $row["COLUMN_NAME"]. "<br>";
    }
    //echo "<pre>";
    //print_r($param);
    $tmpstr = "";

    foreach ($param as $k => $value) {
        $tmpstr .="
* `$k`  $value ";
    }


    foreach ($param as $k => $value) {
        $tmpstr .="
| `data[$k]` | `String` | 否 | $value | ";
    }
    fwrite($myfile, $tmpstr);
}
