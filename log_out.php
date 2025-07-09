<?php
session_start();
session_unset();
session_destroy();
// Cozy temaya uygun çıkış sonrası mesaj için yönlendirme
header("Location: index.php?logout=1");
exit();
?>