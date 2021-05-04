<?
    // Klassendefinition
    class IPS2LOGO_Garagentor extends IPSModule 
    {
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->ConnectParent("{1B0A36F7-343F-42F3-8181-0748819FB324}");
            	$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("Model", 7);
		$this->RegisterPropertyInteger("Address_1", 0);
		$this->RegisterPropertyInteger("Bit_1", 0);
		$this->RegisterPropertyInteger("Output_1", 1);
		$this->RegisterPropertyInteger("Address_2", 0);
		$this->RegisterPropertyInteger("Bit_2", 0);
		$this->RegisterPropertyInteger("Output_2", 1);
		$this->RegisterPropertyInteger("ActuatorID", 0);
		$this->RegisterPropertyInteger("Timer_1", 1000); // Laufzeit des Tastsignals
		$this->RegisterTimer("Timer_1", 0, 'I2LGaragentor_StateReset($_IPS["TARGET"]);'); 
		$this->RegisterPropertyInteger("Timer_2", 250); // Abfrageintervall des Status
		$this->RegisterTimer("Timer_2", 0, 'I2LGaragentor_GetGateState($_IPS["TARGET"]);');
		$this->RegisterPropertyInteger("Timer_3", 5); // Ausschaltverzögerung für die Beleuchtung
		$this->RegisterTimer("Timer_3", 0, 'I2LGaragentor_LightState($_IPS["TARGET"]);');
		$this->RegisterPropertyInteger("WebfrontID", 0);
		$this->RegisterPropertyString("Title", "Meldungstitel");
		$this->RegisterPropertyString("TextOpen", "Text geöffnet");
		$this->RegisterPropertyString("TextClose", "Text geschlossen");
		$this->RegisterPropertyInteger("SoundID", 0);
		$this->RegisterPropertyInteger("Timer_Notify", 15); // Erinnerung wenn Tor geöffnet
		$this->RegisterPropertyInteger("SoundID_Notify", 0);
		$this->RegisterPropertyString("TitleNotify", "Meldungstitel");
		$this->RegisterPropertyString("TextNotify", "Text Erinnerung");
		$this->RegisterTimer("Timer_Notify", 0, 'I2LGaragentor_NotifyState($_IPS["TARGET"]);');
		
		// Profile erstellen
		$this->RegisterProfileInteger("IPS2LOGO.GateState", "Information", "", "", 0, 3, 1);
		IPS_SetVariableProfileAssociation("IPS2LOGO.GateState", 0, "Geöffnet", "Garage", 0xFF0000);
		IPS_SetVariableProfileAssociation("IPS2LOGO.GateState", 25, "Undefiniert", "Garage", 0x0000FF);
		IPS_SetVariableProfileAssociation("IPS2LOGO.GateState", 100, "Geschlossen", "Garage", 0x00FF00);
		
		$this->RegisterProfileInteger("IPS2LOGO.HomekitGateState", "Information", "", "", 0, 3, 1);
		IPS_SetVariableProfileAssociation("IPS2LOGO.HomekitGateState", 0, "Öffnen", "Garage", -1);
		IPS_SetVariableProfileAssociation("IPS2LOGO.HomekitGateState", 1, "Schließen", "Garage", -1);
		IPS_SetVariableProfileAssociation("IPS2LOGO.HomekitGateState", 2, "Öffnet", "Garage", -1);
		IPS_SetVariableProfileAssociation("IPS2LOGO.HomekitGateState", 3, "Schließt", "Garage", -1);
		IPS_SetVariableProfileAssociation("IPS2LOGO.HomekitGateState", 4, "Gestoppt", "Garage", -1);
		
		
		//Status-Variablen anlegen
		$this->RegisterVariableInteger("State", "State", "~ShutterMoveStop", 10);
		$this->EnableAction("State");
		
		$this->RegisterVariableInteger("GateState", "GateState", "IPS2LOGO.GateState", 20);
		
		$this->RegisterVariableInteger("HomekitState", "HomekitState", "IPS2LOGO.HomekitGateState", 30);
		$this->EnableAction("HomekitState");

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
		$arrayElements[] = array("type" => "Label", "caption" => "Auswahl des Netzwerkeingangs Öffnen"); 
		
		$arrayOptions = array();
		for ($i = 0; $i <= 7; $i++) {
			$arrayOptions[] = array("label" => $i, "value" => $i);
		}
		
		$ArrayRowLayout = array();
		$ArrayRowLayout[] = array("type" => "Select", "name" => "Address_1", "caption" => "Adresse", "options" => $arrayOptions );
		$ArrayRowLayout[] = array("type" => "Select", "name" => "Bit_1", "caption" => "Bit", "options" => $arrayOptions );
		$arrayElements[] = array("type" => "RowLayout", "items" => $ArrayRowLayout);
		
		$arrayElements[] = array("type" => "Label", "caption" => "Auswahl des Netzwerkeingangs Schliessen"); 
		
		$ArrayRowLayout = array();
		$ArrayRowLayout[] = array("type" => "Select", "name" => "Address_2", "caption" => "Adresse", "options" => $arrayOptions );
		$ArrayRowLayout[] = array("type" => "Select", "name" => "Bit_2", "caption" => "Bit", "options" => $arrayOptions );

		$arrayElements[] = array("type" => "RowLayout", "items" => $ArrayRowLayout);
		
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "caption" => "Auswahl des digitalen Ausgangs oder Merkers - Tor ist auf"); 
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
		$arrayElements[] = array("type" => "Select", "name" => "Output_1", "caption" => "Ausgang", "options" => $arrayOptions );
		
		$arrayElements[] = array("type" => "Label", "caption" => "Auswahl des digitalen Ausgangs oder Merkers - Tor ist zu"); 
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
		$arrayElements[] = array("type" => "Select", "name" => "Output_2", "caption" => "Ausgang", "options" => $arrayOptions );
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Timer_2", "caption" => "ms", "minumum" => 0);
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "caption" => "Laufzeit des Tast-Impulses");
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Timer_1", "caption" => "ms", "minumum" => 0);
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "caption" => "Beleuchtungs-Aktor-Variable (Boolean)");
            	$arrayElements[] = array("type" => "SelectVariable", "name" => "ActuatorID", "caption" => "Aktor");
		$arrayElements[] = array("type" => "Label", "caption" => "Ausschaltverzögerung für die Beleuchtung");
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Timer_3", "caption" => "s", "minumum" => 0);
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
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "TextOpen", "caption" => "Text geöffnet");
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "TextClose", "caption" => "Text geschlossen");
		$arrayOptions = array();
		$SoundArray = array("Alarm", "Bell", "Boom", "Buzzer", "Connected", "Dark", "Digital", "Drums", "Duck", "Full", "Happy", "Horn", "Inception", "Kazoo", "Roll", "Siren", "Space", "Trickling", "Turn");
		foreach ($SoundArray as $ID => $Sound) {
        		$arrayOptions[] = array("label" => $Sound, "value" => $ID);
    		}
		$arrayElements[] = array("type" => "Select", "name" => "SoundID", "caption" => "Sound", "options" => $arrayOptions );	
		$arrayElements[] = array("type" => "Label", "caption" => "Erinnerungsfunktion für das geöffnete Tor");
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Timer_Notify", "caption" => "min", "minumum" => 0);
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "TitleNotify", "caption" => "Meldungstitel");
		$arrayElements[] = array("type" => "ValidationTextBox", "name" => "TextNotify", "caption" => "Text Erinnerung");
		$arrayOptions = array();
		$SoundArray = array("Alarm", "Bell", "Boom", "Buzzer", "Connected", "Dark", "Digital", "Drums", "Duck", "Full", "Happy", "Horn", "Inception", "Kazoo", "Roll", "Siren", "Space", "Trickling", "Turn");
		foreach ($SoundArray as $ID => $Sound) {
        		$arrayOptions[] = array("label" => $Sound, "value" => $ID);
    		}
		$arrayElements[] = array("type" => "Select", "name" => "SoundID_Notify", "caption" => "Sound", "options" => $arrayOptions );	
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "caption" => "Hinweis: Funktionsweise ist abgestimmt auf die Hörmann Universaladapterplatine (UAP)");
		$arrayElements[] = array("type" => "Label", "caption" => "_____________________________________________________________________________________________________");
		$arrayActions = array(); 
		$arrayActions[] = array("type" => "Label", "caption" => "Test Center"); 
		$arrayActions[] = array("type" => "TestCenter", "name" => "TestCenter");
		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements, "actions" => $arrayActions)); 		 
 	} 
	
	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
                // Diese Zeile nicht löschen
                parent::ApplyChanges();
		
		SetValueInteger($this->GetIDForIdent("State"), 2);
		
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->SetStatus(102);
			If ($this->ReadPropertyInteger("ActuatorID") > 0) {
				// Aktuellen Zustand des Licht einlesen
				$LightState = intval(GetValueBoolean($this->ReadPropertyInteger("ActuatorID")));
				$this->SetBuffer("LightState", $LightState);
			}
			$this->GetGateState();
			$this->SetTimerInterval("Timer_2", $this->ReadPropertyInteger("Timer_2") );
		}
		else {
			$this->SetStatus(104);
			$this->SetTimerInterval("Timer_2", 0 );
		}
		
	}
	
	public function RequestAction($Ident, $Value) 
	{
  		switch($Ident) {
	        case "State":
			$this->KeyPress($Value);
	            	break;
		case "HomekitState":
			$this->SendDebug("RequestAction", "Homekit sendet Wert: ".$Value, 0);
			If ($Value == 0) {
				// Homekit "Open"
				$this->KeyPress(0);
			}
			elseif ($Value == 1) {
				// Homekit "Close"
				$this->KeyPress(4);
			}
	            	break;
		case "RefreshProfileForm":
			$this->SendDebug("RequestAction", "Wert: ".$Value, 0);
			$this->RefreshProfileForm($Value);
			break;
	        default:
	            throw new Exception("Invalid Ident");
	    	}
	}
	    
	public function ReceiveData($JSONString) 
	{
	    	// Empfangene Daten vom Gateway/Splitter
	    	$data = json_decode($JSONString);
	 	$this->SendDebug("ReceiveData", $data, 0);
 	}
	
	// Beginn der Funktionen
	public function SetState(Bool $State, Int $Button)
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true) AND (IPS_GetKernelRunlevel() == KR_READY)) {
			$this->SendDebug("SetState", "Ausfuehrung", 0);
			$this->SetBuffer("Button", $Button);
			$Area = 132; // Konstante
			$Address[0] = $this->ReadPropertyInteger("Address_1"); // Öffnen
			$Bit[0] = $this->ReadPropertyInteger("Bit_1");
			$Address[4] = $this->ReadPropertyInteger("Address_2"); // Schliessen
			$Bit[4] = $this->ReadPropertyInteger("Bit_2");
			
			$AddressBit = ($Address[$Button] * 8) + $Bit[$Button];
			$AddressBit = intval($AddressBit);
			
			If ($State == true) {
				$DataPayload = utf8_encode(chr(1));
			}
			else {
				$DataPayload = utf8_encode(chr(0));
			}
			$this->SetTimerInterval("Timer_1", $this->ReadPropertyInteger("Timer_1"));
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}", "Function" => 5, "Area" => $Area, "AreaAddress" => 0, "BitAddress" => $AddressBit, "WordLength" => 1,"DataCount" => 1,"DataPayload" => $DataPayload)));

			$this->GetGateState();
		}
	}
	        
	public function Keypress(Int $Button)
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {	
			SetValueInteger($this->GetIDForIdent("State"), $Button);
			
			If ($Button == 2) {
				$OldButton = $this->GetBuffer("Button");
				$GateState = GetValueInteger($this->GetIDForIdent("GateState"));
				If (($GateState == 25) AND ($OldButton <> 2)) {
					$this->SetState(true, $OldButton);
					$this->SetBuffer("Button", $Button);
				}
				If ($this->GetValue("HomekitState") <> 4) {
					$this->SetValue("HomekitState", 4);
				}
			}
			else {
				// 0 = Öffnen
				If (($Button == 0) AND ($this->ReadPropertyInteger("ActuatorID") > 0)) {
					// Aktuellen Zustand des Licht einlesen
					$LightState = intval(GetValueBoolean($this->ReadPropertyInteger("ActuatorID")));
					$this->SetBuffer("LightState", $LightState);
					$this->SendDebug("Keypress", "Buffer gesetzt mit: ".$LightState, 0);
					If (boolval($LightState) == false) {
						// Licht einschalten wenn Tor geöffnet wird
						RequestAction($this->ReadPropertyInteger("ActuatorID"), true);
					}
					If ($this->GetValue("HomekitState") <> 2) {
						$this->SetValue("HomekitState", 2);
					}
				}
				// 4 = Schliessen
				If (($Button == 4) AND ($this->ReadPropertyInteger("ActuatorID") > 0)) {
					$LightState = boolval($this->GetBuffer("LightState"));
					$this->SendDebug("Keypress", "Buffer ausgelesen mit: ".$LightState, 0);
					If (boolval($LightState) == false) {
						// Timer starten
						$this->SetTimerInterval("Timer_3", ($this->ReadPropertyInteger("Timer_3") * 1000));
					}
					If ($this->GetValue("HomekitState") <> 3) {
						$this->SetValue("HomekitState", 3);
					}
				}
				$this->SetState(true, $Button);
			}
		}
  	}
	    
	public function StateReset()
	{
		$Button = $this->GetBuffer("Button");
		If ($Button <> 2) {
			$this->SetState(false, $Button);
		}
		$this->SetTimerInterval("Timer_1", 0);
	}
	
	public function GetGateState()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true) AND (IPS_GetKernelRunlevel() == KR_READY)) {
			//$this->SendDebug("GetGateState", "Ausfuehrung", 0);
			$Output = $this->ReadPropertyInteger("Output_1");
			$StateTop = $this->GetState($Output);
			$Output = $this->ReadPropertyInteger("Output_2");
			$StateDown = $this->GetState($Output);
			If (($StateTop == true) AND ($StateDown == false)) {
				$State = 0; // geöffnet
				$HomekitState= 0;
			}
			elseIf (($StateTop == false) AND ($StateDown == true)) {
				$State = 100; // geschlossen
				$HomekitState= 1;
			}
			else {
				$State = 25; // undefinierter Zustand
			}
			If ($this->GetValue("GateState") <> $State) {
				$this->SetValue("GateState", $State);
				If ($State == 0) { // geöffnet
					$this->Notification($this->ReadPropertyString("Title"), $this->ReadPropertyString("TextOpen"), $this->ReadPropertyInteger("SoundID"));
					$this->SetTimerInterval("Timer_Notify", $this->ReadPropertyInteger("Timer_Notify") * 1000 * 60);
				}
				elseif ($State == 100) { //geschlossen
					$this->SetTimerInterval("Timer_Notify", 0);
					$this->Notification($this->ReadPropertyString("Title"), $this->ReadPropertyString("TextClose"), $this->ReadPropertyInteger("SoundID"));
				}
				If ($State <> 25) {
					$this->SetValue("State", 2);
				}
			}
			If ($this->GetValue("HomekitState") <> $HomekitState) {
				$this->SetValue("HomekitState", $HomekitState);
			}
		}
	}
			
	    
	private function GetState(int $Output)
	{
		//$this->SendDebug("GetState", "Ausfuehrung", 0);
		$Result = -1;

		If ($Output < 100) {
			$Area = 130; // Ausgang
			$AreaAddress = 0;
			$BitAddress = $Output - 1;
		}
		else {
			$Area = 131; // Merker
			$AreaAddress = 0;
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
			$Result = $State;
		}
	return $Result;
	}
	
	public function LightState()
	{
		If ($this->ReadPropertyInteger("ActuatorID") > 0) {
			$this->SendDebug("LightState", "Auschaltverzoegerung", 0);
			// Licht ausschalten wenn Tor geschlossen wird
			RequestAction($this->ReadPropertyInteger("ActuatorID"), false);
		}
		$this->SetTimerInterval("Timer_3", 0);
	} 
	
	public function NotifyState()
	{
		$this->SendDebug("NotifyState", "Erinnerung", 0);
		$this->Notification($this->ReadPropertyString("TitleNotify"), $this->ReadPropertyString("TextNotify"), $this->ReadPropertyInteger("SoundID_Notify"));
		
		$this->SetTimerInterval("Timer_Notify", 0);
	}     
	    
	private function Notification ($Title, $Text, $SoundID)
	{
		If ($this->ReadPropertyInteger("WebfrontID") > 0) {
			$WebfrontID = $this->ReadPropertyInteger("WebfrontID");
			$SoundArray = array("Alarm", "Bell", "Boom", "Buzzer", "Connected", "Dark", "Digital", "Drums", "Duck", "Full", "Happy", "Horn", "Inception", "Kazoo", "Roll", "Siren", "Space", "Trickling", "Turn");
			$Sound = strtolower($SoundArray[$SoundID]);
			$TargetID = 0;
			WFC_PushNotification($WebfrontID, $Title, substr($Text, 0, 256), $Sound, $TargetID);
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
        	$this->UpdateFormField('Output_1', 'options', json_encode($arrayOptions));
		$this->UpdateFormField('Output_2', 'options', json_encode($arrayOptions));
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
	   
	private function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
	{
	        if (!IPS_VariableProfileExists($Name))
	        {
	            IPS_CreateVariableProfile($Name, 1);
	        }
	        else
	        {
	            $profile = IPS_GetVariableProfile($Name);
	            if ($profile['ProfileType'] != 1)
	                throw new Exception("Variable profile type does not match for profile " . $Name);
	        }
	        IPS_SetVariableProfileIcon($Name, $Icon);
	        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
	        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);        
	}
	
}
?>
