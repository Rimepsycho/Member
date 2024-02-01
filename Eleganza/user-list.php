<?php

require_once("./db_connect.php");


$perPage = 5;
$orderString = "";  // 初始化排序字符串
// 初始化搜索字符串
$startIndex = "0";
$searchPerformed = false;  // 表示是否执行了搜索
$emptySearch = false;      // 表示搜索内容是否为空


// 排序參數
if (isset($_GET["order"])) {
    $order = (int)$_GET["order"];
} else {
    $order = 1; // 預設排序參數
}


$sqlAll = "SELECT * FROM users WHERE valid=1";
$resultAll = $conn->query($sqlAll);
$userTotalCount = $resultAll->num_rows;

$pageCount = ceil($userTotalCount / $perPage);



// 检查是否提交了搜索请求
if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
    if ($search != '') {
        $searchString = " AND name LIKE '%$search%'";
        $searchPerformed = true;
    } else {
        $emptySearch = true;
    }
}
$rows = [];
$userCount = 0;


// 檢查排序參數並構建排序字符串
if (isset($_GET["order"])) {
    $order = $_GET["order"];
    switch ($order) {
        case 1:
            $orderString = "ORDER BY user_id ASC";
            break;
        case 2:
            $orderString = "ORDER BY user_id DESC";
            break;
        case 3:
            $orderString = "ORDER BY name ASC";
            break;
        case 4:
            $orderString = "ORDER BY name DESC";
            break;
        default:
            $orderString = "ORDER BY user_id ASC"; // 默認排序
    }
}
// 構建搜索條件
$searchString = '';
if (isset($_GET["search"])) {
    $search = $conn->real_escape_string($_GET["search"]);
    $searchString = " AND name LIKE '%$search%'";
}
if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
    if ($search != '') {
        $searchString = " AND name LIKE '%$search%'";
        $searchPerformed = true;
    } else {
        $emptySearch = true;
    }
}




// 計算 startIndex
$p = isset($_GET["p"]) ? max((int)$_GET["p"], 1) : 1;
$startIndex = ($p - 1) * $perPage;


if (isset($_GET["p"])) {
    $p = (int)$_GET["p"]; //用int進行轉換確保顯示是整數(頁數)
    $p = max($p, 1); // 確保頁數不小於1
    $startIndex = ($p - 1) * $perPage;
    // $sql = "SELECT * FROM users WHERE valid=1 $orderString LIMIT $startIndex, $perPage";
} else {
    $p = 1;
    $order = 1;
    $orderString = "ORDER BY user_id ASC";
    // $sql = "SELECT * FROM users WHERE valid=1 $orderString LIMIT $perPage";
}

// 執行分頁查詢
// 构建 SQL 查询

//以下ID是你軟刪除後還會按照ID進行排序，顯示給用戶正確
$sql = "SELECT ROW_NUMBER() OVER (ORDER BY user_id) AS DisplayID, user_id, name, account, phone, email, birth 
        FROM users 
        WHERE valid=1 $searchString 
        $orderString 
        LIMIT $startIndex, $perPage";

$result = $conn->query($sql);
// 执行查询并赋值给 $result



// 检查 $result 是否为 false
if ($result !== false) {
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $userCount = count($rows);
} else {
    echo "處理查询失败：" . $conn->error;
    $userCount = 0; //如果查询失败，设置用户数为0
}

//顯示所有的使用者(總數量)
if (isset($_GET["search"])) {
    $userCount = $result->num_rows;
} else {
    $userCount = $userTotalCount;
}


if (isset($_GET["name"])) {
    $name = $conn->real_escape_string($_GET["name"]);
    $sql = " AND name LIKE '%$name%'";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Eleganza studio (阿爾扎工作室)</title>
   <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="index.php">Eleganza studio(阿爾扎工作室)</a>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
        <!-- Navbar Search-->
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <div class="input-group">
                <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
            </div>
        </form>
        <!-- Navbar-->
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="#!">Settings</a></li>
                    <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item" href="#!">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading text-white">Interface</div>
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                            會員資料
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="./add-user.php">增加會員</a>
                                <a class="nav-link" href="ex.php">第二個清單測試</a>
                                <a class="nav-link" href="./index.php">返回</a>
                            </nav>
                        </div>
                    </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4">會員</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="./index.php">首頁</a></li>
                        <li class="breadcrumb-item active"><a href="./user-list.php">會員清單</a></li>
                    </ol>
                    <div class="card mb-4">
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
                        <!--  -->
                        <div class="card-header">
                            <i class="fas fa-table"></i>
                            會員清單
                            <div>
                                共<?= $userCount ?>人
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div class="py-2 ms-2">
                                <a href="/Eleganza/add-user.php" name="" class="btn btn-primary" role="button"><i class="fa-solid fa-user-plus fa-fw"></i></a>
                            </div>

                            <?php if (isset($_GET["search"])) : ?>
                                <div class="py-2 me-2">
                                    <a name="" id="" class="btn btn-primary" href="./user-list.php" role="button"><i class="fa-solid fa-arrow-left fa-fw"></i></a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if (!isset($_GET["search"])) : ?>
                            <div class="px-2 justify-content-end d-flex align-items-center">
                                <form action="user-list.php" method="get" class="">
                                    <input type="text" name="search" placeholder="搜索用戶...
                                    ">
                                    <button type="submit" class="btn btn-secondary me-3">搜索</button>
                                </form>
                                <div class="me-2">排序</div>
                                <div class="btn-group">
                                    <a class="btn btn-primary" <?php if ($order == 1) echo "active" ?> href="user-list.php?order=1&p=<?= $p ?>">id<i class="fa-solid fa-arrow-down-1-9"></i></a>
                                    <a class="btn btn-primary" <?php if ($order == 2) echo "active" ?> href="user-list.php?order=2&p=<?= $p ?>">id<i class="fa-solid fa-arrow-down-9-1"></i></a>
                                    <a class="btn btn-primary" <?php if ($order == 3) echo "active" ?> href="user-list.php?order=3&p=<?= $p ?>">name<i class="fa-solid fa-arrow-down-a-z"></i></a>
                                    <a class="btn btn-primary" <?php if ($order == 4) echo "active" ?> href="user-list.php?order=4&p=<?= $p ?>">name <i class="fa-solid fa-arrow-down-z-a"></i></a>
                                </div>
                            <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <?php if ($userCount > 0) : ?>
                                    <?php if ($emptySearch) : ?>
                                        <p>請查明後再輸入。</p>
                                    <?php endif; ?>
                                    <table id="" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th><a class="" <?php if ($order == 1) echo "active" ?> href="user-list.php?order=1&p=<?= $p ?>">id</a></th>
                                                <th>姓名</th>
                                                <th>帳號</th>
                                                <th>電話</th>
                                                <th>電子郵件</th>
                                                <th>生日</th>
                                                <th>詳細資訊</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- 等資料庫 -->
                                            <!-- 參考上課users.php -->
                                            <?php
                                            foreach ($rows as $user) :
                                            ?>
                                                <tr>
                                                    <td id="id"><?= $user["DisplayID"] ?></td>
                                                    <td id="name"><?= $user["name"] ?></td>
                                                    <td id="account"><?= $user["account"] ?></td>
                                                    <td id="phone"><?= $user["phone"] ?></td>
                                                    <td id="email"><?= $user["email"] ?></td>
                                                    <td id="birth"><?= $user["birth"] ?></td>
                                                    <td><a class="btn btn-primary" href="user.php?id=<?= $user["user_id"] ?>" role="button"><i class="fa-solid fa-circle-info fa-fw"></i></a>
                                                        <button class="btn btn-danger" data-id="<?= $user["user_id"] ?>" data-bs-toggle="modal" data-bs-target="#confirmModal"><i class="fa-solid fa-trash fa-fw"></i></button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <?php if (!isset($_GET["search"])) : ?>
                                        <nav aria-label="Page navigation example">
                                            <ul class="pagination">
                                                <?php for ($i = 1; $i <= $pageCount; $i++) : ?>
                                                    <li class="page-item <?php if ($i == $p) echo "active"; ?>">
                                                        <a class="page-link" href="user-list.php?order=<?= $order ?>&p=<?= $i ?>"><?= $i ?></a>
                                                    </li>
                                                <?php endfor; ?>
                                            </ul>
                                        </nav>
                                    <?php endif; ?>
                                <?php else : ?>
                                    沒有使用者
                                <?php endif; ?>
                            </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <?php include("../js.php") ?>
    <script>
        var userId = 0;
        $(document).ready(function() {
            // 使用事件委托来处理动态生成的按钮
            $(document).on('click', '.btn-danger', function() {
                userId = $(this).data('id');
                // console.log("用户 ID:", userId);
            });
        });
        function deleteuser() {
                window.location.replace("doDeleteUser.php?id=" + userId);
            }
    </script>
</body>

</html>

<!-- <a class="btn btn-danger" href="doDeleteUser.php?id=?= $user['user_id'] ?>" onclick="return confirm('確定要刪除此使用者嗎？');">
                                                            <i class="fa fa-trash"></i>
                                                        </a> -->