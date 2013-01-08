<?php include '../../common/view/header.html.php';?>
<?php include '../../common/view/sparkline.html.php';?>
<?php include '../../common/view/colorize.html.php';?>
<div class='block' id='productbox'>
    <p><?php echo html::a($this->inlink('create',"productId=$productId"), $lang->wiki->create);?></p>
  <h1><?php echo $revision->name;?></h1>
  <?php echo $revision->content;?>
</div>

<?php include '../../common/view/footer.html.php';?>
