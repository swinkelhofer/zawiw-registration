<?php 
add_action( 'admin_menu', 'menu_insert');
add_action( 'admin_head', 'zawiw_admin_registration_queue_style' );
function menu_insert()
{
	add_dashboard_page("ZAWiW Registration", "ZAWiW Registration", "edit_posts", "zawiw_registration", 'zawiw_registration');
}

function zawiw_registration()
{
?>
	<h2>ZAWiW Registration Blocked Spam Mails</h2>
	<p class="clear">
		Anzahl blockierter Spam-Adressen: <b>
	<?php
		global $wpdb;
		$counter = $wpdb->get_results("SELECT COUNT(*) FROM zawiw_registration_spam_counter WHERE blogPrefix='" . $wpdb->get_blog_prefix(). "'", ARRAY_N);
		$result = $wpdb->get_results("SELECT *, COUNT(*) FROM zawiw_registration_spam_counter WHERE blogPrefix='". $wpdb->get_blog_prefix() . "' GROUP BY spamMail", ARRAY_N);
		echo $counter[0][0];
	?>
	</b>
	</p>
	<h3>Blockierte E-Mail-Adressen</h3>
	<p class="clear">
		<div class="spamPlaceholder">
<?php
	foreach ($result as $res)
	{
		echo "<div class='spamAdress'><a href='http://stopforumspam.com/search/".$res[2] . "' target='#'>";
		echo $res[2] . " (" . $res[3] . ")";
		echo "</a><div>";
	}
?>
		</div>
	</p>
<?php
}

function zawiw_admin_registration_queue_style()
{
	wp_enqueue_style( 'zawiw_admin_registration_style', plugins_url( 'adminstyle.css', __FILE__ ) );
}


?>