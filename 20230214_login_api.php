<?php
//INPUT: {"username":"owner"}
//Output:
// {"state": true, "message":"該帳號不存在, 帳號可以使用!"}
// {"state": false, "message":"該帳號已存在, 帳號不可以使用!"}
// {"state": false, "message":"欄位不得為空白!"}
// {"state": false, "message":"缺少規定欄位!"}

    // 會員登入系統 比對帳號密碼

    $data = file_get_contents("php://input", "r");
    $mydata = array();
    $mydata = json_decode($data, true);

    if(isset($mydata["Username"]) && isset($mydata["Password"])){
        if($mydata["Username"] != ""&& $mydata["Password"] != ""){
            $p_username = $mydata["Username"];
            $p_password = $mydata["Password"];

            require_once("dbtools.php");
            $link = create_connect();
            //找出相同帳號的那一筆資料
            $sql = "SELECT Username, Password, UserState FROM member1 WHERE Username = '$p_username'";
            $result = execute_sql($link, "testdb", $sql);
            if(mysqli_num_rows($result) == 1){ 
            // 定義hash為password_hash 從p_username取得值
            $hash = password_hash($p_username, PASSWORD_DEFAULT);
            $result = execute_sql($link, "testdb", $sql);

                if(mysqli_num_rows($result) == 1){
                    //帳號已經存在 使用password_verify()確認密碼是否正確
                    // 取$p_password, $hash 兩個值做比對
                    // password_verify 解碼
                    $row = mysqli_fetch_assoc($result);
                    $password_hash = $row["Password"];
                        if(password_verify($p_password, $hash)){
                            //密碼驗證成功
                            //產生UID並更新於資料庫
                            $uid01 = substr(md5(hash('sha256', uniqid())),0, 6);
                            $uid02 = substr(md5(hash('sha256', rand())),0, 6);
                            $sql = "UPDATE member1 SET UID01 = '$uid01', UID02 = '$uid02'  WHERE Username = '$p_username'";
                            execute_sql($link, "testdb", $sql);

                            //撈取除了密碼以外的資訊
                            $sql = "SELECT ID, Username, UserState, UID01, UID02 FROM member1 WHERE Username = '$p_username'";
                            $result = execute_sql($link, "testdb", $sql);
                            $row = mysqli_fetch_assoc($result);
                            $userData = array();
                            $userData[] = $row;
                            echo '{"state": true, "message":"登入會員成功","data": '.json_encode($userData).'}';
                        }else{
                            echo '{"state": false, "message":"登入會員失敗'.mysqli_error($link).'"}';
                        }
                }else{
                    //帳號不存在
                    echo '{"state": true, "message":"登入會員失敗'.mysqli_error($link).'"}';
                }
            mysqli_close($link);
        }else{
            echo '{"state": false, "message":"欄位不得為空白!"}';
        }
    }else{
        echo '{"state": false, "message":"缺少規定欄位!"}';
    }
}
?>