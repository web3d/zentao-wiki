<?php include '../../common/view/header.html.php';?>
<?php include '../../common/view/kindeditor.html.php';?>
<script>var holders=<?php echo json_encode($lang->product->placeholder);?></script>
<form method='post' target='hiddenwin' id='dataform'>
  <table class='table-1'> 
    <caption><?php echo $lang->wiki->create;?></caption>
    <tr>
      <th class='rowhead'><?php echo $lang->wiki->name;?></th>
      <td><?php echo html::input('name', '', "class='text-3'");?></td>
    </tr>  
    <tr>
      <th class='rowhead'><?php echo $lang->wiki->content;?></th>
      <td><?php echo html::textarea('content', '', "rows='8' class='area-1'");?></textarea></td>
    </tr>
    <tr>
      <th class='rowhead'><?php echo $lang->wiki->status;?></th>
      <td><?php echo nl2br(html::radio('published', $lang->wiki->publishedOptions, '1'));?></td>
    </tr>
    
    <tr id='whitelistBox' class='hidden'>
      <th class='rowhead'><?php echo $lang->product->whitelist;?></th>
      <td><?php echo html::checkbox('whitelist', $groups);?></td>
    </tr>  
    <tr><td colspan='2' class='a-center'><?php echo html::submitButton();?></td></tr>
  </table>
</form>
<?php include '../../common/view/footer.html.php';?>
