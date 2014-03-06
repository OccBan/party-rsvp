<?php

function party_rsvp_uninstall() {
    global $wpdb;
    
    $thetable = $wpdb->prefix."party_rsvp_events";    
    $wpdb->query("DROP TABLE IF EXISTS $thetable");
    
    $thetable = $wpdb->prefix."party_rsvp_invitees";    
    $wpdb->query("DROP TABLE IF EXISTS $thetable");
}

function party_rsvp_install(){

	global $wpdb;
	
	$table_prefix = $wpdb->prefix . "party_rsvp_";
	
	$tables = array(
		"settings" => $table_prefix . "settings",
		"events" => $table_prefix . "events",
		"invitees" => $table_prefix . "invitees",
		
	);
	
	//settings table
	if($wpdb->get_var("show tables like '" . $tables["settings"] ."'") != $tables["settings"] ) {
	
		$sql = "CREATE TABLE " . $tables["settings"] . " (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		version varchar(255) NOT NULL,
		license varchar(255) NOT NULL,
		UNIQUE KEY id (id)
		);";
		
		$wpdb->query($sql);
		
		$rows_affected = $wpdb->insert( $table_prefix . "settings", array( 'version' => party_rsvp_VERSION, 'license' => 'free version' ) );
	}
	
	//events table
	if($wpdb->get_var("show tables like '" . $tables["events"] . "'") != $tables["events"] ) {
	
		$sql = "CREATE TABLE " . $tables["events"] . " (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		firstName varchar(255) NOT NULL,
		lastName varchar(255) NOT NULL,
		phone varchar(255),
		email varchar(255),
		address varchar(255) NOT NULL,
		city varchar(255) NOT NULL,
		state varchar(3) NOT NULL,
		zip char(5) NOT NULL,
		event_date_time datetime NOT NULL,
		details varchar(255) NOT NULL,
		UNIQUE KEY id (id)
		);";
		
		$wpdb->query($sql);
		
	}

	//invitees table
	if($wpdb->get_var("show tables like '" . $tables["invitees"] . "'") != $tables["settings"] ) {
	
		$sql = "CREATE TABLE " . $tables["invitees"] . " (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		event_id mediumint(9) NOT NULL,
		fname varchar(255) NOT NULL,
		lname varchar(255) NOT NULL,
		email varchar(255) NOT NULL,
		guests mediumint(9),
		response enum('YES', 'NO', 'MAYBE'),
		time_accepted datetime,
		UNIQUE KEY id (id)
		);";
				
		$wpdb->query($sql);
		
	}

}

function build_rsvp_form(){
    global $wpdb;
    
    $id = $wpdb->escape($_REQUEST['invitee_id']);
    $response = $wpdb->escape($_REQUEST['response']); 
    $guests = $wpdb->escape($_REQUEST['guests']); 
    
    if ($response) {
    
        $result = $wpdb->update( $wpdb->prefix . "party_rsvp_invitees", 
					   array( 'response' => $response, 'guests' => $guests ), 
					   array( 'id' => $id )
					  );
    
    ?>
    
        <h2 style="font-size: 32px; margin: 25px;">Thanks!</h2>
        <p style="margin: 30px; font-size: 14px;">Your RSVP has been received. See you at the party!</p>
        <style>.precis{display:none}</style>
    
    <?php
    } else if ($id) {
        $sql = "SELECT * FROM " . $wpdb->prefix . "party_rsvp_invitees WHERE id=" . $id . ";";
        $invitee = $wpdb->get_row($sql, 'ARRAY_A');
        $sql = "SELECT * FROM " . $wpdb->prefix . "party_rsvp_events WHERE id=" . $invitee['event_id'] . ";";
        $event = $wpdb->get_row($sql, 'ARRAY_A');
        $eventDate = new DateTime(stripslashes($event['event_date_time']));
	?>
    <div>
    	
        <div style="float: left; width: 60%;">
        <table cellpadding="3">
            <tr>
                <th valign="top" align="right"><h2>When:</h2></th>
                <td valign="top">
                    <h3><?= $eventDate->format('F j, Y') ?></h3>
                    <p><?= $eventDate->format('h:i a') ?></p>
                    <br/>
                </td>
            </tr>
            <tr>
                <th valign="top" align="right"><h2>Where:</h2></th>
                <td valign="top">
                    <h3>The home of <?= stripslashes($event['firstName']) ?> <?= stripslashes($event['lastName']) ?></h3>
                    <address>
                    <?= stripslashes($event['address']) ?><br/>
                    <?= stripslashes($event['city']) ?>, <?= stripslashes($event['state']) ?> <?= stripslashes($event['zip']) ?><br />
                    <?php if ($event['phone'] != null) { ?><strong>Phone: </strong> <?= stripslashes($event['phone']) ?><br/><?php } ?>
                    <?php if ($event['email'] != null) { ?><strong>Email: </strong> <?= stripslashes($event['email']) ?><?php } ?></address>
                    <br/>
                </td>
            </tr>
            <tr>
                <th valign="top" align="right"><h2>&nbsp;&nbsp;&nbsp;Additional<br/>Info:</h2></th>
                <td valign="top">
                    <p><?= stripslashes($event['details']) ?></p>
                    <br/>
                </td>
            </tr>
        </table>
        </div>
    
        <div style="float: right; width: 40%; background-color: #f7f7f7;">
        <table cellpadding=10><tr><td>
        
        <h2>Fill out the form<br />
        below to RSVP</h2>
        
        <br />
        
        <h3>Will you be attending?</h3>
        <form id="rsvp_form_<?= $event['id'] ?>" action="" method="" onsubmit="return rsvpMe.submitRsvp(<?= $event['id'] ?>)">
        
        <input type='hidden' name='event_id' value='<?= $event['id'] ?>' />
        <input type='hidden' name='invitee_id' value='<?= $invitee['id'] ?>' />
        
        <br />
        
    	<p><input type='radio' name='response' value='YES' id='YES' /><label for='YES'> YES<br/><br />
        <input type='radio' name='response' value='NO' id='NO' /><label for='NO'> NO<br/><br />
        <input type='radio' name='response' value='MAYBE' id='MAYBE' /><label for='MAYBE'> MAYBE</p>
        
        <br />
        
        <h3>Number of guests?</h3>
        <select name="guests" id="guest">
            <option selected>1</option>
            <option>2</option>
            <option>3</option>
            <option>4</option>
        </select>
        
        <br />
        <br />
        
        <input type="image" name="submit" src="<?= WP_PLUGIN_URL ?>/party-rsvp/img/rsvp-small-button.png" value="RSVP" />
        
        <br />
        
        </td></tr></table>
        </div>
        
    </div>
    <?
    } else if ($submit=='RSVP') {
        
        submit_rsvp();
        
    ?>
    
    <h2>Thanks!</h2>
    
    <?php
    }
}

function party_rsvp_get_invitees($id){
	
	global $wpdb;
			
	$rows = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "party_rsvp_invitees
								WHERE event_id = '$id'", ARRAY_A);
	return $rows;
}

function party_rsvp_get_invitee($id){
	
	global $wpdb;
			
	$row = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "party_rsvp_invitees
								WHERE id = '$id'", OBJECT);
	return $row;
}

function party_rsvp_get_event($id){
	
	global $wpdb;
			
	$row = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "party_rsvp_events
								WHERE id = '$id'", OBJECT);
	return $row;
}

function select_state($default=NULL, $field_name='state'){
	
	$state_list = array(
		'AL'=>"Alabama",  
		'AK'=>"Alaska",  
		'AZ'=>"Arizona",  
		'AR'=>"Arkansas",  
		'CA'=>"California",  
		'CO'=>"Colorado",  
		'CT'=>"Connecticut",  
		'DE'=>"Delaware",  
		'DC'=>"District Of Columbia",  
		'FL'=>"Florida",  
		'GA'=>"Georgia",  
		'HI'=>"Hawaii",  
		'ID'=>"Idaho",  
		'IL'=>"Illinois",  
		'IN'=>"Indiana",  
		'IA'=>"Iowa",  
		'KS'=>"Kansas",  
		'KY'=>"Kentucky",  
		'LA'=>"Louisiana",  
		'ME'=>"Maine",  
		'MD'=>"Maryland",  
		'MA'=>"Massachusetts",  
		'MI'=>"Michigan",  
		'MN'=>"Minnesota",  
		'MS'=>"Mississippi",  
		'MO'=>"Missouri",  
		'MT'=>"Montana",
		'NE'=>"Nebraska",
		'NV'=>"Nevada",
		'NH'=>"New Hampshire",
		'NJ'=>"New Jersey",
		'NM'=>"New Mexico",
		'NY'=>"New York",
		'NC'=>"North Carolina",
		'ND'=>"North Dakota",
		'OH'=>"Ohio",  
		'OK'=>"Oklahoma",  
		'OR'=>"Oregon",  
		'PA'=>"Pennsylvania",  
		'RI'=>"Rhode Island",  
		'SC'=>"South Carolina",  
		'SD'=>"South Dakota",
		'TN'=>"Tennessee",  
		'TX'=>"Texas",  
		'UT'=>"Utah",  
		'VT'=>"Vermont",  
		'VA'=>"Virginia",  
		'WA'=>"Washington",  
		'WV'=>"West Virginia",  
		'WI'=>"Wisconsin",  
		'WY'=>"Wyoming");
		
	$select = "<select name='$field_name' class='required'>\n";
	$select .= "<option value=''>Select A State</option>\n";
	
	foreach($state_list as $value => $name){
	
		if(strtolower($default) == strtolower($value) || strtolower($default) == strtolower($name))
			$select .= "<option value='" . $value . "' selected='selected'>" . $name . "</option>\n";
		else
			$select .= "<option value='" . $value . "'>" . $name . "</option>\n";
	
	}
	
	$select .= "</option>\n";
	
	return $select;
}

function party_rsvp_event_form($handle, $event=NULL){
	
	if($event){
		$timestamp = strtotime($event['event_date_time']);
		$date = date("m/d/Y", $timestamp);
		$hour = date("h", $timestamp);
		$minute = date("i", $timestamp); 
		$meridian = date("a", $timestamp);
	}	
	?>
	<div id='admin-wrapper'>
        <h2><?=ucfirst($handle)?> A Party</h2>
        
		<form id="rsvp_add_edit_form" action="" method="post" name="" class="validateThis">
		
        <?= $handle=='edit' ? "<input type='hidden' name='id' value='" . $event["id"] ."' />\n" : "" ?>
        
		<div class='form-segments'>
		<h4>Event Details</h4>
  
		<table cellpadding="10" cellpadding="5">
			
			<tr>
				<td align='right' valign='top'>First name</td><td><input type='text' id='text' class="required" name='firstName' value='<?=stripslashes($event['firstName'])?>' /></td>
			</tr>
            
			<tr>
				<td align='right' valign='top'>Last name</td><td><input type='text' id='text' class="required" name='lastName' value='<?=stripslashes($event['lastName'])?>' /></td>
			</tr>
            
			<tr>
				<td align='right' valign='top'>Phone</td><td><input type='text' id='text' class="required" name='phone' value='<?=stripslashes($event['phone'])?>' /></td>
			</tr>
            
			<tr>
				<td align='right' valign='top'>Email</td><td><input type='text' id='text' class="required" name='email' value='<?=stripslashes($event['email'])?>' /></td>
			</tr>
			
			<tr>
				<td align='right' valign='top'>Address</td><td><textarea name='address' class="required" id='address'><?=$event['address']?></textarea></td>
			</tr>
			
			<tr>
				<td align='right' valign='top'>City</td><td><input type='text' name='city' class="required" id='city' value='<?=$event['city']?>' /></td>
			</tr>
			
			<tr>
				<td align='right' valign='top'>State</td><td><?= select_state($event['state']) ?></td>
			</tr>
			
			<tr>
				<td align='right' valign='top'>Zip</td><td><input type='text' name='zip' class="required" size='5' maxlength="5" value='<?=$event['zip']?>' /></td>
			</tr>  
			
		</table>
		</div>
		
		<div class='form-segments'>
		<h4>Date & Time</h4>
		<table cellpadding="10" cellpadding="5">
			<tr>
				<td>
				Date (MM/DD/YY)<br />
				<input type="text" name="date" class="required" size='10' maxlength="10" value="<?= $date ?>" title="Date" class='reqd' />
				</td>
			
				<td>
				Time<br />
				<select name='hour'>
					<?
					for($i=1; $i < 13; $i++){
						$h = ($i < 10 ? "0" . $i : $i);
						echo "<option value='$h' " . ($hour == $h ? "selected='selected'" : "") . ">$h</option>\n";
					} 
					?>
				</select>
				<select name='minute'>
					<?
					for($i=0; $i < 61; $i++){
						$min = ($i < 10 ? "0" . $i : $i);
						echo "<option value='$min' " . ($minute == $min ? "selected='selected'" : "") . ">$min</option>\n";
					} 
					?>
				</select>
				<select name='meridian'>
			   
				  <option value='am' <?= $meridian == "am" ? "selected='selected'" : "" ?>>AM</option>
				  <option value='pm' <?= $meridian == "pm" ? "selected='selected'" : "" ?>>PM</option>
				
				 </select>
				</td>
			</tr>
			
		</table>
		</div>
		
        <div class='form-segments'>
		<h4>More Information</h4>
  
		<table cellpadding="10" cellpadding="5" width="100%">
			
			<tr>
				<td><textarea id='text' name='details' value='<?=stripslashes($event['details'])?>' style="width: 100%; height: 80px;"><?=$event['details']?></textarea></td>
			</tr>
			
		</table>
		</div>
		
        <?php
        if ($handle!='edit')
        {
        ?>
        
        <div class='form-segments'>
		<h4>Invitees</h4>
  
		<table cellpadding="0" cellpadding="0" width="100%" >
			
			<tr>
				<th align='left' valign='top'>First name</th>
                <th align='left' valign='top'>Last name</th>
				<th align='left' valign='top'>Email</th>
			</tr>
            
            <tr>
				<td><input type='text' id='inviteeFirstName' name='inviteeFirstName[0]' value='' style="width: 100px;" /></td>
                <td><input type='text' id='inviteeLastName' name='inviteeLastName[0]' value='' style="width: 150px;" /></td>
				<td><input type='text' id='inviteeEmail' name='inviteeEmail[0]' value='' style="width: 190px;"/></td>
			</tr>
            
            <tr>
                <td colspan="3" align="right"><input type="button" id="addInvitee" name="addInvitee" value="+ Add Invitee" /></td>
            </tr>
			
		</table>
		</div>
        
        <?php } ?>
	  
		<p><input type='submit' name='submit' value='Submit' /></p>
	   
		</form>
		
	</div>
	<? 
}
?>