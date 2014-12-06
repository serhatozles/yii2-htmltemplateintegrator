<?php
/* @var $this yii\web\View */
/* @var $results string */
/* @var $hasError boolean */
$this->beginContent(__DIR__ . '/layouts/main.php');
?>
<div class="default-view-results">
    <?php
    echo '<div class="alert alert-success">' . nl2br($message) . '</div>';
    ?>
    <pre><?= nl2br($results) ?></pre>
</div>
<?php $this->endContent(); ?>