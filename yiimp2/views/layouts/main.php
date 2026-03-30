<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

use app\models\Mining;

AppAsset::register($this);

$pageTitle = empty($this->title) ? YAAMP_SITE_NAME : YAAMP_SITE_NAME." - ".$this->title;

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);

$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', ['media' => 'all']);
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/v4-shims.min.css', ['media' => 'all']);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($pageTitle) ?></title>
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header id="header">
    <?php
    NavBar::begin([
        'brandLabel' => '<i class="fas fa-cube"></i> ' . YAAMP_SITE_NAME,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => ['class' => 'navbar-expand-md navbar-dark bg-dark fixed-top']
    ]);

    $mining = Mining::find()->one();
	$nextpayment = date('H:i T', $mining->last_payout+YAAMP_PAYMENTS_FREQ);
	$eta = ($mining->last_payout+YAAMP_PAYMENTS_FREQ) - time();
	$eta_mn = 'in '.round($eta / 60).' minutes';

    $items_navbar = [
            ['label' => 'Home', 'url' => ['/site/index']],
            ['label' => 'Pool', 'url' => ['/site/mining']],
            ['label' => 'Wallet', 'url' => ['/?address=']],
            ['label' => 'Graphs', 'url' => ['/stats'], 'active' => in_array(\Yii::$app->controller->id, ['stats']),],
            ['label' => 'Miners', 'url' => ['/site/miners']],
            ['label' => 'API', 'url' => ['/site/api']],
            ['label' => 'Explorers', 'url' => ['/explorer'], 'active' => in_array(\Yii::$app->controller->id, ['explorer']),],
    ];

    if ((!is_null(Yii::$app->user->identity)) && (Yii::$app->user->identity->is_admin)) {
        $admin_navbar = [
            '&nbsp;&nbsp;&nbsp;&nbsp;',
            ['label' => 'Dashboard', 'url' => ['/admin/dashboard']],
            ['label' => 'Wallets', 'url' => ['/admin/coinwallets']],
            ['label' => 'Coins', 'url' => ['/admin/coinlist']],
             '<li class="nav-item">'
                    . Html::beginForm(['/admin/logout'])
                    . Html::submitButton(
                        'Logout (' . Yii::$app->user->identity->username . ')',
                        ['class' => 'nav-link btn btn-link logout']
                    )
                    . Html::endForm()
                    . '</li>'
        ];
    }
    else {
         $admin_navbar = [
            ['label' => 'Login', 'url' => ['/admin/login'], 'active' => in_array(\Yii::$app->controller->id, ['admin']),]
         ];
    }

    $items = array_merge($items_navbar, $admin_navbar);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav'],
        'items' => $items
    ]);
    echo Html::tag('div', 'Next Payout: '.$nextpayment, 
                    ['class' => 'navbar-text ms-auto' ]);
    NavBar::end();
   ?>
</header>

<main id="main" class="flex-shrink-0" role="main">
    <div class="container">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
        <?php endif ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer id="footer" class="mt-auto py-4 bg-dark text-white">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3 mb-md-0">
                <h5><?=YAAMP_SITE_NAME?></h5>
                <p class="text-muted">Professional multi-algorithm cryptocurrency mining pool.</p>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="/site/mining" class="text-muted">Mining</a></li>
                    <li><a href="/site/block" class="text-muted">Blocks</a></li>
                    <li><a href="/site/api" class="text-muted">API</a></li>
                    <li><a href="/explorer" class="text-muted">Explorer</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Support</h5>
                <a href="https://discord.gg/DrsrWQh3qC" class="text-muted me-2" target="_blank"><i class="fab fa-discord fa-lg"></i></a>
                <a href="https://t.me/yourchannel" class="text-muted" target="_blank"><i class="fab fa-telegram fa-lg"></i></a>
            </div>
        </div>
        <hr class="bg-secondary">
        <div class="row">
            <div class="col-12 text-center text-muted">
                <small>&copy; <?php echo date('Y').' '.YAAMP_SITE_NAME ?>. All rights reserved. Powered by <a href="https://github.com/Kudaraidee/yiimp" class="text-muted">YIIMP</a></small>
            </div>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
