<?php




include 'includes/dashboard/header.php';
if($_SESSION['log_type'] == 'A'){
    //Load Administrator Menu File
    include 'includes/dashboard/menu_administrator.php';
}else{
    //Load Researcher Menu File
    include 'includes/dashboard/menu_researcher.php';
}
include 'includes/dashboard/navbar.php';
$this->content(); //aqui será carregado o conteúdo da página
include 'includes/dashboard/footer.php';

?>
