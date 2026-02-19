<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? 'Cloud 9 Cafe';
$extraCssFiles = $extraCssFiles ?? [];
if (!is_array($extraCssFiles)) {
    $extraCssFiles = [$extraCssFiles];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Nunito+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/cloud_9_cafe/assets/css/main.css">
    <?php foreach ($extraCssFiles as $cssFile): ?>
        <?php if (is_string($cssFile) && $cssFile !== ''): ?>
            <link rel="stylesheet" href="<?php echo htmlspecialchars($cssFile, ENT_QUOTES, 'UTF-8'); ?>">
        <?php endif; ?>
    <?php endforeach; ?>
</head>
<body>
