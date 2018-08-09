<?php
/* Smarty version 3.1.31, created on 2017-07-17 17:13:55
  from "/var/www/html/shop4/includes/vendor/jtlshop/shop4-wizard/src/templates/gui.tpl" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.31',
  'unifunc' => 'content_596cd4335e9196_81022210',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '18be1d519567b0a67b68ad8b331f094cf70a6606' => 
    array (
      0 => '/var/www/html/shop4/includes/vendor/jtlshop/shop4-wizard/src/templates/gui.tpl',
      1 => 1500304431,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_596cd4335e9196_81022210 (Smarty_Internal_Template $_smarty_tpl) {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Rudimentäre Shop4 Wizard GUI</title>
</head>
<body>
    <h1>Shop4-Wizard</h1>
    <h2><?php echo $_smarty_tpl->tpl_vars['wizard']->value->getTitle();?>
</h2>
    <form action="gui.php" method="post">
        <input type="hidden" name="stepId" value="<?php echo $_smarty_tpl->tpl_vars['wizard']->value->getStepId();?>
">
        <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['wizard']->value->getQuestions(), 'question', false, 'questionId');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['questionId']->value => $_smarty_tpl->tpl_vars['question']->value) {
?>
            <h3><?php echo $_smarty_tpl->tpl_vars['question']->value->getText();?>
</h3>
            <?php if ($_smarty_tpl->tpl_vars['question']->value->getType() === 0) {?>
                <input type="checkbox" name="question-<?php echo $_smarty_tpl->tpl_vars['questionId']->value;?>
"
                       <?php if ($_smarty_tpl->tpl_vars['question']->value->getValue()) {?>checked<?php }?>>
            <?php } elseif ($_smarty_tpl->tpl_vars['question']->value->getType() === 1) {?>
                <input type="text" name="question-<?php echo $_smarty_tpl->tpl_vars['questionId']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['question']->value->getValue();?>
">
            <?php }?>
        <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl, 1);
?>

        <p>
            <button type="submit" name="submit" value="yes">Weiter</button>
        </p>
    </form>
</body>
</html><?php }
}
