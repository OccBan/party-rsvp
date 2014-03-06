<?
global $wpdb;
	
add_action('admin_print_styles', 'add_styles');

add_action('admin_menu', 'party_rsvp_plugin_menu');

add_action('admin_init', 'party_rsvp_admin_scripts');

add_action('admin_head', 'party_rsvp_admin_header');

add_action('wp_ajax_party_rsvp_delete_event', 'party_rsvp_delete_event');

function party_rsvp_admin_header(){
	?>
    <script type="text/javascript" src="http://jzaefferer.github.com/jquery-validation/jquery.validate.js"></script>
	<script type="text/javascript">
	
	var $ = jQuery;
	
	$(document).ready(function(){
        $('#addInvitee').click(function(){
            var s = $(this).parent().parent().siblings().size() - 1;
            $(this).parent().parent().before("<tr><td><input type='text' id='inviteeFirstName' name='inviteeFirstName[" + s + "]' value='' style='width: 100px;' /></td><td><input type='text' id='inviteeLastName' name='inviteeLastName[" + s + "]' value='' style='width: 150px;' /></td><td><input type='text' name='inviteeEmail[" + s + "]' id='inviteeEmail' value='' style='width: 190px;'/></td></tr>");
        });
        $(".validateThis").validate();
	});
    
	function party_rsvp_delete_event(id){
		var data = {
			action : 'party_rsvp_delete_event',
			id : id
		};
		$.post(ajaxurl, data, function(response) {
			$("#eventrow_"+id).remove();
			$("#rsvp_ajax_msg").html("Event successfully removed");
		});	
	}
		
	function toggle_rsvps(id){
	
		if( $("#event_rsvps_"+id).css("display") == "none" ){
			$("#event_rsvps_"+id).show();
		}else{
			$("#event_rsvps_"+id).hide();
		}
		
	}	
	</script>
	<?	
}

function party_rsvp_admin_scripts(){
	wp_enqueue_script('jquery');
}

function party_rsvp_plugin_menu() {
  
	$top_menu_slug = "rsvp_events_overview";
	
	add_menu_page('PARTY RSVP Events', 'Parties', 'manage_options', $top_menu_slug, 'party_rsvp_events_overview');
	
	add_submenu_page( $top_menu_slug, 'Add A Party', 'Add A Party', 'manage_options', 'party_rsvp_add_event', 'party_rsvp_add_event');
	
	add_submenu_page( '', 'Edit Party', 'Add A Party', 'manage_options', 'party_rsvp_edit_event', 'party_rsvp_edit_event');
	
	add_submenu_page( '', 'Delete Party', 'Delete Party', 'manage_options', 'party_rsvp_delete_event', 'party_rsvp_delete_event');
	
	add_submenu_page( '', 'Send Invite', 'Send Invite', 'manage_options', 'party_rsvp_resend_invite', 'party_rsvp_resend_invite');

}

function party_rsvp_events_overview(){
	?>
	<div id='admin-wrapper'>
		<h1>Parties</h1>
		
		<?php
		
		global $wpdb;
		
		$sql = "SELECT * FROM " . $wpdb->prefix . "party_rsvp_events ORDER BY id DESC";
		
		$rows = $wpdb->get_results($sql);
		
		?>
        <span id='rsvp_ajax_msg'></span>
		<div id="admin-events-wrapper">	
            <table cellpadding="0" cellspacing="0">
				<tr>
					<th>Host&nbsp;Name</th>
					<th>Location</th>
					<th>Date&nbsp;&amp;&nbsp;time</th>
                    <th>Details</th>
                    <th>Invitees</th>
					<th></th>
				</tr>
				<?php
				foreach($rows as $row){
				
					$rsvps = party_rsvp_get_invitees($row->id);					
					$rsvp_count = count($rsvps);
		
					echo "<tr id='eventrow_" . $row->id . "'>\n";
					echo "<td valign='top'>" . stripslashes($row->firstName) . "&nbsp;" . stripslashes($row->lastName) . "</td>\n";
					echo "<td valign='top'>" . stripslashes($row->address) . ", " . stripslashes($row->city) . ", " . stripslashes($row->state) . " " . stripslashes($row->zip) . "</td>\n";
					echo "<td valign='top' nowrap>" . date("F jS g:i a", strtotime($row->event_date_time)) . "</td>\n";
                    echo "<td valign='top'>" . stripslashes($row->details) . "</td>\n";
					echo "<td valign='top'><a href='Javascript: toggle_rsvps($row->id)'>" . $rsvp_count . ($rsvp_count > 1 ? "&nbsp;invitees" : "&nbsp;invitee") . "</a></td>\n";
					echo "<td valign='top'><a href='?page=party_rsvp_edit_event&id=" . $row->id . "'>Edit</a>&nbsp;|&nbsp;";
					echo "<a href='Javascript: party_rsvp_delete_event(" . $row->id . ")' onclick='confirm(\"Are you sure you want to permanently delete this event?\")'>Delete</a></td>";
					echo "</tr>\n";
					
					party_rsvp_build_event_rsvps($rsvps, $row->id);
					
					echo "<tr><td colspan='6'><div style='width:100%; height:2px; border-bottom: 1px solid #ccc'></div></td></tr>\n";
				}
				?>
			</table>
		</div>
		
	</div>
	<? 
}

function party_rsvp_build_event_rsvps($rsvps, $id){
	
	?>
	<tr class='event_rsvps' id='event_rsvps_<?= $id?>' style='display:none'>
        <td colspan='6'>
        <div style="border: 1px solid #ddd;">
        <table width='100%' cellpadding='5'>
        	<tr>
            	<th>Invitee</th>
                <th>Email</th>
                <th>Response</th>
                <th>Guests</th>
                <th></th>
            </tr>
        	<? $count = count($rsvps); for($i=0; $i < $count; $i++): ?>
                <tr>
                	<td><?= $rsvps[$i]['fname'] . " " . $rsvps[$i]['lname'] ?></td>
                    <td><?= $rsvps[$i]['email'] ?></td>
                    <td><?= $rsvps[$i]['response'] ?></td>
                    <td><?= $rsvps[$i]['guests'] ?></td>
                    <td><a href="/party-rsvp?invitee_id=<?= $rsvps[$i]['id'] ?>" target='_blank'>View</a>&nbsp;|&nbsp;
                    <a href="?page=party_rsvp_resend_invite&id=<?= $rsvps[$i]['id'] ?>">Send Invite</a></td>
                </tr>
            <? endfor; ?>   
            
            <tr>
            	<td colspan="5">
                	<a href='Javascript: toggle_rsvps(<?= $id ?>)'>Close</a>
                </td>
            </tr>
        </table>
        </div>
        </td>
    </tr>
    <?
}

function party_rsvp_add_event(){
	
	global $wpdb;
	$wpdb->show_errors();
    
	if($_POST){
        foreach($_POST as $field => $value) ${$field} = $value;
		
		$date_time_obj = new DateTime($date . " " . ($meridian == "pm" ? ($hour + 12) : $hour) . ":" . $minute . ":00");
        $date_time = $date_time_obj->format('Y-m-d H:i:s');
        
		$result1 = $wpdb->query( $wpdb->prepare( "
						INSERT INTO " . $wpdb->prefix . "party_rsvp_events
						( id, firstName, lastName, phone, email, address, city, state, zip, event_date_time, details )
						VALUES ( %d, %s, %s, %s, %s, %s, %s, %s, %d, %s, %s )", 
						array(NULL, $firstName, $lastName, $phone, $email, $address, $city, $state, $zip, $date_time, $details) 
						) 
					);												   
		
        $eventId = $wpdb->insert_id;
        $result2 = 0;
        
        for ($i=0; $i<sizeof($inviteeFirstName); $i++) {
            if ($inviteeFirstName[$i] != "" && $inviteeLastName[$i] != "" && $inviteeEmail[$i] != "") {
            
            $result2 = $wpdb->query( $wpdb->prepare( "
                            INSERT INTO " . $wpdb->prefix . "party_rsvp_invitees
                            ( id, event_id, fname, lname, email )
                            VALUES ( %d, %d, %s, %s, %s )", 
                            array(NULL, $eventId, $inviteeFirstName[$i], $inviteeLastName[$i], $inviteeEmail[$i]) 
                            ) 
                        );
            $inviteeId = $wpdb->insert_id;
            if ($result2) {
                $headers = "From: " . $email . "\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
                $theName = $inviteeFirstName[$i] . " " . $inviteeLastName[$i];
                
                $theDate = new DateTime($date_time);
                
                $message = file_get_contents(dirname(__FILE__) . '/includes/email.html');
                
                $message = str_replace("[[ID]]", $inviteeId, $message);
                
                $message = str_replace("[[THE_INVITEE]]", $theName, $message);
                
                $message = str_replace("[[THE_DATE]]", $theDate->format('F j, Y'), $message);
                $message = str_replace("[[THE_TIME]]", $theDate->format('h:i a'), $message);
                $message = str_replace("[[THE_HOST]]", $firstName . ' ' . $lastName, $message);
                
                $message = str_replace("[[ADDRESS]]", $address, $message);
                $message = str_replace("[[CITY]]", $city, $message);
                $message = str_replace("[[STATE]]", $state, $message);
                $message = str_replace("[[ZIP]]", $zip, $message);
                
                $message = str_replace("[[PHONE]]", $phone, $message);
                $message = str_replace("[[EMAIL]]", $email, $message);
                
                $message = str_replace("[[DETAILS]]", $details, $message);
                
                mail($inviteeEmail[$i], "You’re Invited!", $message, $headers);
                }
            } else {
                $result1 = 0;
            }
        }
        
        if ($result1) {
            echo "<h2>Event added successfully</h2>\n";
        } else {
            echo "<h2>Event was not added</h2>\n";
        }
	}
	
	party_rsvp_event_form('add');

}

function party_rsvp_edit_event(){
	
	global $wpdb;
	
	$id = $wpdb->escape($_REQUEST['id']);
	
	if($_POST){
		foreach($_POST as $field => $value) ${$field} = $value;			
		
		$date_time_obj = new DateTime($date . " " . ($meridian == "pm" ? ($hour + 12) : $hour) . ":" . $minute . ":00");
        $date_time = $date_time_obj->format('Y-m-d H:i:s');
		
		$wpdb->update( $wpdb->prefix . "party_rsvp_events", 
					   array( 'firstName' => $firstName, 'lastName' => $lastName, 'phone' => $phone, 'email' => $email, 'address' => $address, 
							  'city' => $city, 'state' => $state, 'zip' => $zip, 'event_date_time' => $date_time, 'details' => $details ), 
					   array( 'id' => $id )
					  );

		
		echo "<h2>Event edited successfully</h2>\n";
	}
	
	$sql = "SELECT * FROM " . $wpdb->prefix . "party_rsvp_events WHERE id='$id'";
	
	$eventdata = $wpdb->get_row($sql, 'ARRAY_A');
	
	party_rsvp_event_form('edit', $eventdata);

}

function party_rsvp_delete_event(){
	
	global $wpdb;
	
	$id = $wpdb->escape($_REQUEST['id']);
	
	$sql = "DELETE FROM " . $wpdb->prefix . "party_rsvp_events WHERE id='$id' LIMIT 1";
	
	$wpdb->query($sql);
	
	?>
	<h2>Party successfully removed</h2>
	<?

}

function party_rsvp_resend_invite(){
	
    global $wpdb;
	
	$id = $wpdb->escape($_REQUEST['id']);
    
	$invite = party_rsvp_get_invitee($id);
    $event = party_rsvp_get_event($invite->event_id);
    
    $headers = "From: " . $event->email . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    
    $theName = $invite->fname . " " . $invite->lname;
                
    $theDate = new DateTime($event->date_time);
                
    $message = file_get_contents(dirname(__FILE__) . '/includes/email.html');
                
    $message = str_replace("[[ID]]", $invite->id, $message);
    
    $message = str_replace("[[THE_INVITEE]]", $theName, $message);
                
    $message = str_replace("[[THE_DATE]]", $theDate->format('F j, Y'), $message);
    $message = str_replace("[[THE_TIME]]", $theDate->format('h:i a'), $message);
    $message = str_replace("[[THE_HOST]]", $event->firstName . ' ' . $event->lastName, $message);
                
    $message = str_replace("[[ADDRESS]]", $event->address, $message);
    $message = str_replace("[[CITY]]", $event->city, $message);
    $message = str_replace("[[STATE]]", $event->state, $message);
    $message = str_replace("[[ZIP]]", $event->zip, $message);
                
    $message = str_replace("[[PHONE]]", $event->phone, $message);
    $message = str_replace("[[EMAIL]]", $event->email, $message);
                
    $message = str_replace("[[DETAILS]]", $event->details, $message);
                
    $status = mail($invite->email, "You’re Invited!", $message, $headers);
	
	?>
	<h2>Invite sent - <?= $invite->id; ?></h2>
	<?

}
?>