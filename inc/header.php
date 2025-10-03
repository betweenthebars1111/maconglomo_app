<?php
// inc/header.php
require_once __DIR__ . '/../config/session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAConglomo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .table {
            table-layout: fixed;
            width: 100%;
        }

        .table th,
        .table td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: center;
            /* default center */
        }

        /* Make only the first column (Generic Name) left-aligned */
        .table th:nth-child(1),
        .table td:nth-child(1) {
            text-align: left;
            width: 20%;
        }

        .table th:nth-child(2),
        .table td:nth-child(2) {
            width: 20%;
        }

        .table th:nth-child(3),
        .table td:nth-child(3) {
            width: 10%;
        }

        .table th:nth-child(4),
        .table td:nth-child(4) {
            width: 15%;
        }

        .table th:nth-child(5),
        .table td:nth-child(5) {
            width: 15%;
        }

        .table th:nth-child(6),
        .table td:nth-child(6) {
            width: 10%;
        }

        .table th:nth-child(7),
        .table td:nth-child(7) {
            width: 10%;
        }
    </style>

</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="/maconglomo_app/public/index.php">MAConglomo</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="/maconglomo_app/public/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/maconglomo_app/public/login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">