<?php

/** @var yii\web\View $this */

use app\models\Coins;
use app\models\Mining;

$this->registerJsFile('@web/js/auto_refresh.js', ['depends' => [yii\web\JqueryAsset::className()]]);

$homeUrl = Yii::$app->homeUrl;

$min_payout = floatval(YAAMP_PAYMENTS_MINI);
$min_sunday = $min_payout / 10;
$payout_freq = (YAAMP_PAYMENTS_FREQ / 3600) . " hours";

$mining = Mining::find()->one();
?>

<style>
.hero-section {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    color: white;
    padding: 60px 0;
    margin-bottom: 40px;
    border-radius: 0 0 20px 20px;
}
.hero-section h1 {
    font-size: 2.5rem;
    margin-bottom: 20px;
}
.hero-section p {
    font-size: 1.1rem;
    opacity: 0.9;
}
.feature-card {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s;
    height: 100%;
}
.feature-card:hover {
    transform: translateY(-5px);
}
.feature-icon {
    font-size: 3rem;
    margin-bottom: 15px;
}
.quick-links {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
}
.quick-links a {
    display: inline-block;
    padding: 10px 20px;
    margin: 5px;
    background: #0d6efd;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    transition: background 0.3s;
}
.quick-links a:hover {
    background: #0b5ed7;
    color: white;
}
.stratum-box {
    background: #1e1e1e;
    color: #00ff00;
    padding: 20px;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    margin: 15px 0;
}
.coin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 10px;
    margin: 20px 0;
}
.coin-item {
    text-align: center;
    padding: 10px;
    border-radius: 8px;
    background: #f8f9fa;
    transition: all 0.3s;
}
.coin-item:hover {
    background: #e9ecef;
    transform: scale(1.05);
}
.coin-item img {
    width: 40px;
    height: 40px;
}
.coin-item span {
    display: block;
    font-size: 0.75rem;
    margin-top: 5px;
}
.generator-card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}
</style>

<div id='resume_update_button' style='color: #444; background-color: #ffd; border: 1px solid #eea;
    padding: 10px; margin: 20px 0; cursor: pointer; display: none; border-radius: 8px;'
    onclick='auto_page_resume();' align=center>
    <b>Auto refresh is paused - Click to resume</b></div>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1><?=YAAMP_SITE_NAME?></h1>
                <p>Professional multi-algorithm cryptocurrency mining pool. Mine your favorite coins with low fees and high performance servers.</p>
                <p><strong>Payouts:</strong> Every <?= $payout_freq ?> for balances above <b><?= $min_payout ?></b></p>
            </div>
            <div class="col-lg-5 text-lg-end">
                <a href="https://discord.gg/DrsrWQh3qC" target="_blank" class="btn btn-discord btn-lg me-2">
                    <i class="fab fa-discord"></i> Discord
                </a>
                <a href="/site/api" class="btn btn-outline-light btn-lg">API</a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="quick-links">
    <div class="row text-center">
        <div class="col-12">
            <h5 class="mb-3">Quick Links</h5>
            <a href="/">Home</a>
            <a href="/site/mining">Start Mining</a>
            <a href="/site/block">Blocks</a>
            <a href="/site/miners">Statistics</a>
            <a href="/site/api">API</a>
            <a href="/explorer">Explorer</a>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <!-- Left Column - Stratum Generator -->
    <div class="col-lg-6">
        <div class="generator-card">
            <h3 class="mb-4"><i class="fas fa-hammer"></i> Stratum Generator</h3>
            
            <div class="mb-3">
                <label class="form-label">Algorithm</label>
                <select id="drop-algo" class="form-select" onchange="updateCoinsForAlgo()">
                    <?php
                    $algos = (new \yii\db\Query())
                        ->select(['name'])
                        ->from('algos')
                        ->orderBy('name')
                        ->all();
                    foreach($algos as $a):
                    ?>
                    <option value="<?= $a['name'] ?>"><?= $a['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Coin</label>
                <select id="drop-coin" class="form-select" onchange="generate()">
                    <?php
                    $list = Coins::find()
                        ->where(['enable' => 1, 'visible' => 1, 'auto_ready' => 1])
                        ->orderBy(['algo' => SORT_ASC, 'name' => SORT_ASC])
                        ->all();

                    if (!$list) {
                        echo "<option disabled>No Coins Available</option>";
                    } else {
                        foreach ($list as $coin) {
                            $name = substr($coin->name, 0, 18);
                            $symbol = $coin->getOfficialSymbol();
                            $algo = $coin->algo;
                            $port = Yii::$app->YiimpUtils->getCoinPort($coin);
                            $auto_exchange = isset($coin->auto_exchange) ? $coin->auto_exchange : 1;
                            $mc_param = ($auto_exchange == 0) ? ",mc=$symbol" : "";
                            
                            echo "<option value='$symbol' data-port='$port' data-algo='$algo' data-extra='-p c=$symbol$mc_param'>$name ($symbol)</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Wallet Address</label>
                <input id="text-wallet" type="text" class="form-control" placeholder="Enter your wallet address" onkeyup="generate()">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Worker Name (Optional)</label>
                <input id="text-rig-name" type="text" class="form-control" placeholder="e.g. rig1" onkeyup="generate()">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Mining Type</label>
                <select id="drop-solo" class="form-select" onchange="generate()">
                    <option value="">Shared</option>
                    <option value=",m=solo">Solo</option>
                </select>
            </div>
            
            <div class="stratum-box">
                <code id="output">-a scrypt -o stratum+tcp://<?=YAAMP_STRATUM_URL?>:3333 -u WALLET_ADDRESS.WORKER_NAME -p c=SYMBOL</code>
            </div>
            
            <div class="alert alert-info mt-3">
                <small><i class="fas fa-info-circle"></i> Use your wallet address as username. Worker name is optional but recommended for tracking.</small>
            </div>
        </div>
        
        <!-- Available Coins -->
        <div class="generator-card">
            <h4 class="mb-3"><i class="fas fa-coins"></i> Available Coins</h4>
            <div class="coin-grid">
                <?php
                foreach($list as $coin):
                    $symbol = $coin->getOfficialSymbol();
                    $image = $coin->image ?: '/images/btc.png';
                ?>
                <div class="coin-item" onclick="selectCoin('<?= $symbol ?>')" style="cursor:pointer;">
                    <img src="<?= $image ?>" alt="<?= $symbol ?>" onerror="this.src='/images/btc.png'">
                    <span><?= $symbol ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Right Column - Pool Stats -->
    <div class="col-lg-6">
        <div class="generator-card">
            <h3 class="mb-4"><i class="fas fa-chart-line"></i> Pool Statistics</h3>
            <div id="pool_stats_loading">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div id="pool_current_results"></div>
        </div>
        
        <div class="generator-card">
            <h4 class="mb-3"><i class="fas fa-history"></i> Recent Blocks</h4>
            <div id="pool_history_results"></div>
        </div>
        
        <div class="generator-card">
            <h4 class="mb-3"><i class="fas fa-chart-pie"></i> Pool Coins</h4>
            <div id="pool_coins_info"></div>
        </div>
    </div>
</div>

<!-- Why Choose Us -->
<div class="row mt-4 mb-5">
    <div class="col-12 text-center mb-4">
        <h2>Why Choose Us?</h2>
    </div>
    <div class="col-md-4 mb-3">
        <div class="feature-card">
            <div class="feature-icon">🛡️</div>
            <h5>Secure & Reliable</h5>
            <p>DDoS protected servers with 99.9% uptime guarantee</p>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="feature-card">
            <div class="feature-icon">💰</div>
            <h5>Low Fees</h5>
            <p>Competitive pool fees starting at 0.5%</p>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="feature-card">
            <div class="feature-icon">⚡</div>
            <h5>Fast Payouts</h5>
            <p>Automatic payments every <?= $payout_freq ?></p>
        </div>
    </div>
</div>

<script>
function generate() {
    var coin = document.getElementById('drop-coin');
    var wallet = document.getElementById('text-wallet').value.trim();
    var rigName = document.getElementById('text-rig-name').value.trim();
    var solo = document.getElementById('drop-solo').value;
    
    if (!coin || !coin.selectedIndex) return;
    
    var selectedOption = coin.options[coin.selectedIndex];
    var algo = selectedOption.dataset.algo;
    var port = selectedOption.dataset.port;
    var extra = selectedOption.dataset.extra;
    var symbol = selectedOption.value;
    
    var result = '-a ' + algo + ' -o stratum+tcp://<?=YAAMP_STRATUM_URL?>:' + port + ' -u ';
    result += wallet ? wallet + (rigName ? '.' + rigName : '.WORKER') : 'WALLET_ADDRESS' + (rigName ? '.' + rigName : '.WORKER');
    result += ' ' + extra + solo;
    
    document.getElementById('output').innerHTML = result;
}

function selectCoin(symbol) {
    var select = document.getElementById('drop-coin');
    for (var i = 0; i < select.options.length; i++) {
        if (select.options[i].value === symbol) {
            select.selectedIndex = i;
            generate();
            break;
        }
    }
}

function updateCoinsForAlgo() {
    // This function can be enhanced to filter coins by selected algo
    generate();
}

function page_refresh() {
    pool_current_refresh();
    pool_history_refresh();
    pool_coins_info_refresh();
}

function pool_current_ready(data) {
    $('#pool_current_results').html(data);
}

function pool_current_refresh() {
    var url = "<?php echo $homeUrl ?>site/current_results";
    $.get(url, '', pool_current_ready);
}

function pool_history_ready(data) {
    $('#pool_history_results').html(data);
}

function pool_history_refresh() {
    var url = "<?php echo $homeUrl ?>site/history_results";
    $.get(url, '', pool_history_ready);
}

function pool_coins_info_ready(data) {
    $('#pool_coins_info').html(data);
}

function pool_coins_info_refresh() {
    var url = "<?php echo $homeUrl ?>site/coins_info";
    $.get(url, '', pool_coins_info_ready);
}

// Initialize
$(document).ready(function() {
    generate();
    page_refresh();
});
</script>
