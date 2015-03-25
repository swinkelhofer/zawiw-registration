<?php 
function zawiw_registration_activation()
{
	$creation_query = "CREATE TABLE zawiw_registration_spam_counter (
      id int(20) NOT NULL AUTO_INCREMENT,
      blogPrefix TEXT NOT NULL,
      spamMail TEXT NOT NULL,
      UNIQUE KEY id (id)
      ) DEFAULT CHARACTER SET=utf8;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $creation_query );
}
?>