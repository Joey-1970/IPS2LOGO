<?
    // Klassendefinition
    class IPS2LOGO_Klingel extends IPSModule 
    {
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->RegisterMessage(0, IPS_KERNELMESSAGE);
		
		$this->ConnectParent("{1B0A36F7-343F-42F3-8181-0748819FB324}");
            	$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("Model", 7);
		$this->RegisterPropertyInteger("Output", 1);
		$this->RegisterPropertyInteger("Address", 0);
		$this->RegisterPropertyInteger("Bit", 0);
		$this->RegisterPropertyInteger("Switchtime", 20);
		$this->RegisterPropertyInteger("Resettime", 1);
		$this->RegisterTimer("Reset", 0, 'I2LKlingel_Reset($_IPS["TARGET"]);');
		$this->RegisterPropertyInteger("Timer_1", 250);
		$this->RegisterTimer("Timer_1", 0, 'I2LKlingel_GetState($_IPS["TARGET"]);');
		$this->RegisterPropertyInteger("Sorting", 3);
		$this->RegisterPropertyInteger("WebfrontID", 0);
		$this->RegisterPropertyString("Title", "Meldungstitel");
		$this->RegisterPropertyString("Text", "Text");
		$this->RegisterPropertyInteger("SoundID", 0);
		$this->RegisterAttributeString("EventData", ""); 
		
		//Status-Variablen anlegen
		$this->RegisterVariableBoolean("State", "State", "~Switch", 10);
		$this->RegisterVariableString("Log", "Log", "~HTMLBox", 20);
		
		$EventData = array();
		$this->WriteAttributeString("EventData", serialize($EventData)); 
        }
       	
	public function GetConfigurationForm() { 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
		$arrayStatus[] = array("code" => 200, "icon" => "error", "caption" => "Instanz ist fehlerhaft"); 
		$arrayStatus[] = array("code" => 202, "icon" => "error", "caption" => "Kommunikationfehler!");
		
		$arrayElements = array(); 
		$arrayElements[] = array("name" => "Open", "type" => "CheckBox",  "caption" => "Aktiv"); 
		$arrayOptions = array();
		$arrayOptions[] = array("label" => "LOGO 7", "value" => 7);
		$arrayOptions[] = array("label" => "LOGO 8", "value" => 8);
		$arrayElements[] = array("type" => "Select", "name" => "Model", "caption" => "Modell", "options" => $arrayOptions, "onChange" => 'IPS_RequestAction($id,"RefreshProfileForm",$Model);' );

 		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "caption" => "Auswahl des Netzwerkeingangs");
		
		$arrayOptions = array();
		for ($i = 0; $i <= 7; $i++) {
			$arrayOptions[] = array("label" => $i, "value" => $i);
		}
		
		$ArrayRowLayout = array();
		$ArrayRowLayout[] = array("type" => "Select", "name" => "Address", "caption" => "Adresse", "options" => $arrayOptions );
		$ArrayRowLayout[] = array("type" => "Select", "name" => "Bit", "caption" => "Bit", "options" => $arrayOptions );
		$arrayElements[] = array("type" => "RowLayout", "items" => $ArrayRowLayout);
		
		$arrayElements[] = array("type" => "Label", "caption" => "Laufzeit des Tast-Impulses");
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Switchtime", "caption" => "ms", "minimum" => 20);
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________"); 
		$arrayElements[] = array("type" => "Label", "caption" => "Auswahl des digitalen Ausgangs oder Merkers"); 
		
		$arrayOptions = array();
		If ($this->ReadPropertyInteger("Model") == 7) {
			for ($i = 1; $i <= 16; $i++) {
				$arrayOptions[] = array("label" => "Q".$i, "value" => $i);
			}
			for ($i = 1; $i <= 27; $i++) {
				$arrayOptions[] = array("label" => "M".$i, "value" => ($i + 100));
			}
		}
		else If ($this->ReadPropertyInteger("Model") == 8) {
			for ($i = 1; $i <= 20; $i++) {
				$arrayOptions[] = array("label" => "Q".$i, "value" => $i);
			}
			for ($i = 1; $i <= 64; $i++) {
				$arrayOptions[] = array("label" => "M".$i, "value" => ($i + 100));
			}
		}
		
		$arrayElements[] = array("type" => "Select", "name" => "Output", "caption" => "Ausgang", "options" => $arrayOptions );
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Timer_1", "caption" => "ms");
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "caption" => "Reset des Klingelsignals");
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Resettime", "caption" => "s", "minimum" => 1);
		
		$arrayOptions = array();
		$arrayOptions[] = array("label" => "Neuste Nachricht zuerst", "value" => SORT_DESC);
		$arrayOptions[] = array("label" => "Älteste Nachricht zuerst", "value" => SORT_ASC);
		$arrayElements[] = array("type" => "Select", "name" => "Sorting", "caption" => "Sortierung in der Darstellung", "options" => $arrayOptions );

		
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "caption" => "Benachrichtigungsfunktion");
		$WebfrontID = Array();
		$WebfrontID = $this->GetWebfrontID();
		$arrayOptions = array();
		$arrayOptions[] = array("label" => "unbestimmt", "value" => 0);
		foreach ($WebfrontID as $ID => $Webfront) {
        		$arrayOptions[] = array("label" => $Webfront, "value" => $ID);
    		}
		$arrayElements[] = array("type" => "Select", "name" => "WebfrontID", "caption" => "Webfront", "options" => $arrayOptions );
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "Title", "caption" => "Meldungstitel");
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "Text", "caption" => "Text");
		$arrayOptions = array();
		$SoundArray = array("Alarm", "Bell", "Boom", "Buzzer", "Connected", "Dark", "Digital", "Drums", "Duck", "Full", "Happy", "Horn", "Inception", "Kazoo", "Roll", "Siren", "Space", "Trickling", "Turn");
		foreach ($SoundArray as $ID => $Sound) {
        		$arrayOptions[] = array("label" => $Sound, "value" => $ID);
    		}
		$arrayElements[] = array("type" => "Select", "name" => "SoundID", "caption" => "Sound", "options" => $arrayOptions );		
		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	} 
	
	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
                // Diese Zeile nicht löschen
                parent::ApplyChanges();
		
		if (IPS_GetKernelRunlevel() == KR_READY) {
			// Webhook einrichten
			$this->RegisterHook("/hook/IPS2LOGOKlingel_".$this->InstanceID);
		}
		
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->Reset();
			$this->GetState();
			$this->SetStatus(102);
			$EventData = array();
			$EventData = unserialize($this->ReadAttributeString("EventData"));
			$this->RenderData($EventData);
			$this->SetTimerInterval("Timer_1", $this->ReadPropertyInteger("Timer_1") );
		}
		else {
			$this->SetStatus(104);
			$this->SetTimerInterval("Timer_1", 0);
		}
		
	}
	
	public function RequestAction($Ident, $Value) 
	{
  		switch($Ident) {
		case "RefreshProfileForm":
				$this->SendDebug("RequestAction", "Wert: ".$Value, 0);
				$this->RefreshProfileForm($Value);
			break;
	        default:
	            throw new Exception("Invalid Ident");
	    	}
	}

	// Beginn der Funktionen
	private function SetState(Bool $State)
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {
			$this->SendDebug("SetState", "Ausfuehrung", 0);
			$Area = 132; // Konstante
			$Address = $this->ReadPropertyInteger("Address");
			$Bit = $this->ReadPropertyInteger("Bit");
			
			$AddressBit = ($Address * 8) + $Bit;
			$AddressBit = intval($AddressBit);
			
			If ($State == true) {
				$DataPayload = utf8_encode(chr(1));
			}
			else {
				$DataPayload = utf8_encode(chr(0));
			}
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}", "Function" => 5, "Area" => $Area, "AreaAddress" => 0, "BitAddress" => $AddressBit, "WordLength" => 1,"DataCount" => 1,"DataPayload" => $DataPayload)));
			//$this->SendDebug("SetState", "Ergebnis: ".intval($Result), 0);
			$this->GetState();
		}
	}
	    
	public function GetState()
	{
		$State = false;
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true) AND (IPS_GetKernelRunlevel() == KR_READY)) {
			//$this->SendDebug("GetState", "Ausfuehrung", 0);
			$Output = $this->ReadPropertyInteger("Output");
			$AreaAddress = 0;
			
			If ($Output < 100) {
				$Area = 130; // Ausgang
				$BitAddress = $Output - 1;
			}
			else {
				$Area = 131; // Merker
				$BitAddress = $Output - 101;
			}
				
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}", "Function" => 4, "Area" => $Area, "AreaAddress" => $AreaAddress, "BitAddress" => $BitAddress, "WordLength" => 1, "DataCount" => 1,"DataPayload" => "")));
			If ($Result === false) {
				$this->SetStatus(202);
				$this->SendDebug("GetState", "Fehler bei der Ausführung!", 0);
			}
			else {
				$this->SetStatus(102);
				$State = ord($Result);
				//$this->SendDebug("GetState", "Ergebnis: ".$State, 0);
				If ($State <> GetValueBoolean($this->GetIDForIdent("State"))) {
					SetValueBoolean($this->GetIDForIdent("State"), $State);
					If ($State == true) {
						$this->SendDebug("GetState", "Es hat geklingelt!", 0);
						$this->WorkProcess("Add", microtime(true));
						$this->Notification($this->ReadPropertyString("Text"));
						$this->SetTimerInterval("Reset", $this->ReadPropertyInteger("Resettime") * 1000);
					}
				}
			}
		}
	return $State;
	}
	
	public function Reset()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {	
			$Switchtime = $this->ReadPropertyInteger("Switchtime"); // Dauer der Betätigung
			$this->SetState(true);
			IPS_Sleep($Switchtime);
			$this->SetState(false);
			$this->SetTimerInterval("Reset", 0);
		}
  	}   
	
	private function WorkProcess(string $Activity, int $EventID) 
	{
		If ($this->ReadPropertyBoolean("Open") == true) {
			if (IPS_SemaphoreEnter("WorkProcess", 2000))
			{
				$EventData = array();
				$EventData = unserialize($this->ReadAttributeString("EventData"));
				switch ($Activity) {
					case 'Add':
						$EventData[$EventID]["EventID"] = $EventID;
		
						$EventData[$EventID]["Timestamp"] = microtime(true);
						$this->SendDebug("WorkProcess", "Event ".$EventID." wurde hinzugefuegt", 0);
						break;
					case 'Remove':
						If (is_array($EventData)) {
							if (array_key_exists($EventID, $EventData)) {
								unset($EventData[$EventID]);
								$this->SendDebug("WorkProcess", "Event ".$EventID." wurde entfernt", 0);
							}
							else {
								$this->SendDebug("WorkProcess", "Event ".$EventID." wurde nicht gefunden", 0);
							}
						}
						break;
					case 'RemoveAll':
						$EventData = array();
						$this->SendDebug("WorkProcess", "Alle Messages wurde entfernt", 0);
						break;
					case 'Switch':
						/*
						If (is_array($MessageData)) {
							if (array_key_exists($MessageID, $MessageData)) {
								If ((intval($MessageData[$MessageID]["WebfrontID"]) >= 10000) AND (strlen($MessageData[$MessageID]["Page"]) > 0)) {
									$this->SendDebug("WorkProcess", "Switch Webfront: ".$MessageData[$MessageID]["WebfrontID"]." Item: ".$MessageData[$MessageID]["Page"], 0);
									WFC_SwitchPage (intval($MessageData[$MessageID]["WebfrontID"]), $MessageData[$MessageID]["Page"]);
								}
							}
						}
						*/
						break;
				}
				$this->WriteAttributeString("EventData", serialize($EventData));
			}
			IPS_SemaphoreLeave("WorkProcess");
			If ($Activity <> "AutoRemove") {
				$this->RenderData($EventData);
			}
		}
		else {
			$this->SendDebug("WorkProcess", "Semaphore Abbruch!", 0);
		}
	}   
	    
	protected function ProcessHookData() 
	{		
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->SendDebug("ProcessHookData", "Ausfuehrung: ".$_SERVER['HOOK'], 0);
			switch ($_GET['action']) {
			    	case 'remove':
			      		$EventID = isset($_GET['EventID']) ? $_GET['EventID'] : -1;
			      		if ($EventID > 0) {
						$this->WorkProcess("Remove", $EventID);
			      		}
					else {
						$this->SendDebug("ProcessHookData", "Keine EventID!", 0);
					}
			      		break;
			    case 'switch':
			      		$MessageID = isset($_GET['EventID']) ? $_GET['EventID'] : -1;
			      		if ($MessageID > 0) {
						$this->WorkProcess("Switch", $EventID);
			      		}
					else {
						$this->SendDebug("ProcessHookData", "Keine EventID!", 0);
					}
			      		break;
			      break;
			}
		}
	}       
	    
	private function RenderData($EventData)		
	{
		$Sorting = $this->ReadPropertyInteger("Sorting");
		
		// Etwas CSS und HTML
		$style = "";
		$style .= '<style type="text/css">';
		$style .= 'table { width:100%; border-collapse: collapse; }';
		$style .= 'td.fst { width: 36px; padding: 2px; border-left: 1px solid rgba(255, 255, 255, 0.2); border-top: 1px solid rgba(255, 255, 255, 0.1); }';
		$style .= 'td.mid { padding: 2px;  border-top: 1px solid rgba(255, 255, 255, 0.1); }';
		$style .= 'td.lst { width: 42px; text-align:center; padding: 2px;  border-right: 1px solid rgba(255, 255, 255, 0.2); border-top: 1px solid rgba(255, 255, 255, 0.1); }';
		$style .= 'tr:last-child { border-bottom: 1px solid rgba(255, 255, 255, 0.2); }';
		$style .= '.blue { padding: 5px; color: rgb(255, 255, 255); background-color: rgb(0, 0, 255); background-image: linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -o-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -moz-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -webkit-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -ms-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); }';
		$style .= '.red { padding: 5px; color: rgb(255, 255, 255); background-color: rgb(255, 0, 0); background-image: linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -o-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -moz-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -webkit-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -ms-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); }';
		$style .= '.green { padding: 5px; color: rgb(255, 255, 255); background-color: rgb(0, 255, 0); background-image: linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -o-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -moz-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -webkit-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -ms-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); }';
		$style .= '.yellow { padding: 5px; color: rgb(255, 255, 255); background-color: rgb(255, 255, 0); background-image: linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -o-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -moz-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -webkit-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -ms-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); }';
		$style .= '.orange { padding: 5px; color: rgb(255, 255, 255); background-color: rgb(255, 160, 0); background-image: linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -o-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -moz-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -webkit-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); background-image: -ms-linear-gradient(top,rgba(0,0,0,0) 0,rgba(0,0,0,0.3) 50%,rgba(0,0,0,0.3) 100%); }';
		$style .= '</style>';

		$content = $style;
		$content .= '<table>';
		
		if (count($EventData) == 0) {
			$content .= '<tr>';
			$Icon = "Ok";
			$content .= '<td class="iconMediumSpinner ipsIcon' .$Icon. '"></td>';
			$content .= '<td class="lst">'.date("d.m.Y H:i", time() ).'</td>';
			$content .= '<td class="mid">Keine Meldungen vorhanden!</td>';
			$content .= '<td class="mid"></td>';
			$content .= '<td class=\'lst\'><div class=\'green\' onclick=\'alert("Nachricht kann nicht bestätigt werden.");\'>...</div></td>';
			$content .= '</tr>';
	  	}
	  	else {
	    		$MessageData =  $this->EventSort($EventData, 'Timestamp',  $Sorting);
			foreach ($EventData as $Number => $Event) {
	      			$TypeColor = "red"; // array("green", "red", "yellow", "blue");
				$TypeImage = "Alert"; //array("Ok", "Alert", "Warning", "Clock");
				$Event['Type'] = 0;
				//$Event['Type'] = min(3, max(0, $Event['Type']));
						
				
				$Image = $TypeImage;
				

				$content .= '<tr>';
				$content .= '<td class="iconMediumSpinner ipsIcon' .$Image. '"></td>';
				
				$SecondsToday= date('H') * 3600 + date('i') * 60 + date('s');
				If ($Event['Timestamp'] <= (time() - $SecondsToday)) {
					$content .= '<td class="lst">'.date("d.m.Y H:i", $Event['Timestamp']).'</td>';
				}
				else {
					$content .= '<td class="lst">'.date("H:i:s", $Event['Timestamp']).'</td>';
				}
				
				$content .= '<td class="mid">'.utf8_decode("Es hat geklingelt!").'</td>';
				
				$content .= '<td class="mid"></td>';
				
				$content .= '<td class=\'lst\'><div class=\''.$TypeColor.'\' onclick="window.xhrGet=function xhrGet(o) {var HTTP = new XMLHttpRequest();HTTP.open(\'GET\',o.url,true);HTTP.send();};window.xhrGet({ url: \'hook/IPS2LOGOKlingel_'.$this->InstanceID.'?ts=\' + (new Date()).getTime() + \'&action=remove&EventID='.$Event['EventID'].'\' });">OK</div></td>';
					
				$content .= '</tr>';
			}
	  	}
	  	$content .= '</table>';
	  	If (GetValueString($this->GetIDForIdent("Log")) <> $content) {
			SetValueString($this->GetIDForIdent("Log"), $content);
		}
	}        
	    
	private function RefreshProfileForm($Model)
    	{
        	$arrayOptions = array();
		If ($Model == 7) {
			for ($i = 1; $i <= 16; $i++) {
				$arrayOptions[] = array("label" => "Q".$i, "value" => $i);
			}
			for ($i = 1; $i <= 27; $i++) {
				$arrayOptions[] = array("label" => "M".$i, "value" => ($i + 100));
			}
		}
		else If ($Model == 8) {
			for ($i = 1; $i <= 20; $i++) {
				$arrayOptions[] = array("label" => "Q".$i, "value" => $i);
			}
			for ($i = 1; $i <= 64; $i++) {
				$arrayOptions[] = array("label" => "M".$i, "value" => ($i + 100));
			}
		}
        	$this->UpdateFormField('Output', 'options', json_encode($arrayOptions));
		$this->UpdateFormField('Output_AP', 'options', json_encode($arrayOptions));
    	}    
	
	private function Notification ($Text)
	{
		If ($this->ReadPropertyInteger("WebfrontID") > 0) {
			$WebfrontID = $this->ReadPropertyInteger("WebfrontID");
			$Title = $this->ReadPropertyString("Title");
			$SoundID = $this->ReadPropertyInteger("SoundID");
			$SoundArray = array("Alarm", "Bell", "Boom", "Buzzer", "Connected", "Dark", "Digital", "Drums", "Duck", "Full", "Happy", "Horn", "Inception", "Kazoo", "Roll", "Siren", "Space", "Trickling", "Turn");
			$Sound = strtolower($SoundArray[$SoundID]);
			$TargetID = 0;
			WFC_PushNotification($WebfrontID, $Title, substr($Text, 0, 256), $Sound, $TargetID);
		}
	}        
	
	private function EventSort($EventData, $DataField, $SortOrder) 
	{
    		if(is_array($EventData)==true) {
            		foreach ($EventData as $key => $value) {
            			if(is_array($value) == true){
                			foreach ($value as $kk => $vv) {
                    				${$kk}[$key]  = strtolower( $value[$kk]);
                			}
            			}
        		}
    		}
    		array_multisort(${$DataField}, $SortOrder, $EventData);
    	return $EventData;
	}    
	    
	private function GetWebfrontID()
	{
    		$guid = "{3565B1F2-8F7B-4311-A4B6-1BF1D868F39E}"; // Webfront Konfigurator
    		//Auflisten
    		$WebfrontArray = (IPS_GetInstanceListByModuleID($guid));
    		$Result = array();
    		foreach ($WebfrontArray as $Webfront) {
        		$Result[$Webfront] = IPS_GetName($Webfront);
    		}
	return $Result;   
	}        
	    
	private function GetParentID()
	{
		$ParentID = (IPS_GetInstance($this->InstanceID)['ConnectionID']);  
	return $ParentID;
	}
  	
  	private function GetParentStatus()
	{
		$Status = (IPS_GetInstance($this->GetParentID())['InstanceStatus']);  
	return $Status;
	}
	    
	private function RegisterHook($WebHook)
    	{
        	$ids = IPS_GetInstanceListByModuleID('{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}');
        	if (count($ids) > 0) {
            		$hooks = json_decode(IPS_GetProperty($ids[0], 'Hooks'), true);
            		$found = false;
            		foreach ($hooks as $index => $hook) {
                		if ($hook['Hook'] == $WebHook) {
                    			if ($hook['TargetID'] == $this->InstanceID) {
                        			return;
                    			}
                    			$hooks[$index]['TargetID'] = $this->InstanceID;
                    			$found = true;
                		}
            		}
            		if (!$found) {
                		$hooks[] = ['Hook' => $WebHook, 'TargetID' => $this->InstanceID];
            		}
            		IPS_SetProperty($ids[0], 'Hooks', json_encode($hooks));
            		IPS_ApplyChanges($ids[0]);
		}
        }
}
?>
