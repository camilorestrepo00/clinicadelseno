<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrador') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $con = connection();
    
    $tipo_identificacion = $_POST['tipo_identificacion'];
    $identificacion = $_POST['identificacion'];
    $nombres_apellidos = $_POST['nombres_apellidos'];
    $cargo = $_POST['cargo'];
    $tipo_cargo = $_POST['tipo_cargo'];
    $horas = $_POST['horas'];
    $sede = $_POST['sede'];
    $tipo_contrato = $_POST['tipo_contrato'];
    $renovacion = $_POST['renovacion'] ?: NULL;

    $sql = "INSERT INTO contratos (
        tipo_identificacion,
        identificacion,
        nombres_apellidos,
        cargo,
        tipo_cargo,
        horas,
        sede,
        tipo_contrato,
        renovacion
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "sssssssss",
        $tipo_identificacion,
        $identificacion,
        $nombres_apellidos,
        $cargo,
        $tipo_cargo,
        $horas,
        $sede,
        $tipo_contrato,
        $renovacion
    );

    if (mysqli_stmt_execute($stmt)) {
        header('Location: index.php?seccion=contratos&msg=success');
    } else {
        header('Location: index.php?seccion=contratos&msg=error');
    }
    exit;
}