<!doctype html>
<html lang="es">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<meta name="mobile-web-app-capable" content="yes">
		<title><?= $_institution['nombre']; ?></title>
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto">
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans">
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway">
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat">
		<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
		<link rel="stylesheet" href="<?= css; ?>/bootstrap-4.3.1.min.css">
		<link rel="stylesheet" href="<?= css; ?>/animate.min.css">
		<link rel="stylesheet" href="<?= css; ?>/checkfood.min.css">
		<link rel="icon" type="image/png" href="favicon.png">
	</head>
	<body>
		<?php if (environment == 'production') : ?>
		<div id="loader" class="loader-wrapper loader-wrapper-fixed">
			<div class="loader-wrapper-backdrop">
				<span class="loader"></span>
			</div>
		</div>
		<?php endif ?>
		<div class="container-fluid">