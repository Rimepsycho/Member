<?php

require_once("./db_connect.php");

$sql = "SELECT name, user_id, account, phone, email, birth FROM users WHERE valid=1";
$result = $conn->query($sql);

// 將結果提取到陣列中
$rows = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
}
//以下ID是你軟刪除後還會按照ID進行排序，顯示給用戶正確
// $sql = "SELECT ROW_NUMBER() OVER (ORDER BY user_id) AS DisplayID, user_id, name, account, phone, email, birth 
//         FROM users 
//         WHERE valid=1 $searchString 
//         $orderString 
//         LIMIT $startIndex, $perPage";

// $result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>資料表格操作範例</title>

    <!-- jQuery Library -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <!-- icon -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="container">
        <table id="table_id" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>姓名</th>
                    <th>帳號</th>
                    <th>電話</th>
                    <th>電子郵件</th>
                    <th>生日</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $user) : ?>
                    <tr>
                        <td><?= $user["user_id"] ?></td>
                        <td><?= $user["name"] ?></td>
                        <td><?= $user["account"] ?></td>
                        <td><?= $user["phone"] ?></td>
                        <td><?= $user["email"] ?></td>
                        <td><?= $user["birth"] ?></td>
                        <td><button class='edit-btn' data-id='<?= $user["user_id"] ?>'>編輯</button>
                            <button type="button" class='delete-btn' data-id='<?= $user["user_id"] ?>' data-bs-toggle="modal" data-bs-target="#staticBackdrop">刪除</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Model -->
        <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">刪除使用者</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        確認刪除?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button role="button" class="btn btn-danger" onclick="deleteuser()">確認</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            var userId = 0;
            $(document).ready(function() {
                $('#table_id').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Chinese-traditional.json",
                        "info": "顯示第 _START_ 項到第 _END_ 項結果，共 _TOTAL_ 項記錄",
                        "paginate": {
                            "previous": "<i class='fa fa-chevron-left'></i>",
                            "next": "<i class='fa fa-chevron-right'></i>",

                        }
                    },
                    "columnDefs": [{
                        "targets": -1, // 目标列为最后一列
                        "data": null, // 不从服务器获取数据
                        "defaultContent": "<button class='edit-btn'>編輯</button> <button class='delete-btn'>刪除</button>"
                    }],
                    "pageLength": 10, // 設定默認每頁顯示的行數
                    "lengthMenu": [
                        [5, 10, 20, -1],
                        [5, 10, 20, "All"]
                    ], // 設定可選擇的每頁顯示行數選項
                    "columnDefs": [{
                        "targets": -1,
                        "data": null,
                        "defaultContent": "<button class='edit-btn'>編輯</button> <button class='delete-btn'>刪除</button>"
                    }],
                });

                $('#table_id tbody').on('click', 'button.edit-btn', function() {
                    var data = $('#table_id').DataTable().row($(this).parents('tr')).data();
                    userId = data[0];
                    // console.log(userId);
                    window.location.replace("user-edit.php?id=" + userId);
                });


                $('#table_id tbody').on('click', 'button.delete-btn', function() {
                    var data = $('#table_id').DataTable().row($(this).parents('tr')).data();
                    userId = data[0]; // data[0] 代表第一列的数据，根据实际情况调整
                    window.location.replace("doDeleteUser.php?id=" + userId);
                    // $("#confirm-delete").attr("href", "doDeleteUser.php?id=" + userId);
                });


            });
        </script>
</body>

</html>