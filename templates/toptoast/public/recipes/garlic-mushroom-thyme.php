<?php $config = require __DIR__ . '/../../includes/config.php'; ?>
<?php $recipes = require __DIR__ . '/../../includes/recipes.php'; ?>
<?php
$recipe = $recipes['garlic-mushroom-thyme'];
$pageTitle = $recipe['title'];
$pageDescription = $recipe['description'];
?>
<?php include __DIR__ . '/../../includes/header.php'; ?>
<?php include __DIR__ . '/../../includes/recipe-template.php'; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
