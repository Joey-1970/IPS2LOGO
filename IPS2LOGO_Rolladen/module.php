<?
    // Klassendefinition
    class IPS2LOGO_Rolladen extends IPSModule 
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
		$this->RegisterPropertyInteger("Address_2", 0);
		$this->RegisterPropertyInteger("Bit_2", 0);
		$this->RegisterPropertyInteger("Timer_1", 30);
		$this->RegisterTimer("Timer_1", 0, 'I2LRolladen_StateReset($_IPS["TARGET"]);');
		$this->RegisterPropertyInteger("ActualTemperatureID", 0);
		$this->RegisterPropertyFloat("Temperature", 3.0);
		$this->RegisterPropertyInteger("WebfrontID", 0);
		$this->RegisterPropertyString("Title", "Meldungstitel");
		$this->RegisterPropertyString("TextOpen", "Text geöffnet");
		$this->RegisterPropertyString("TextClose", "Text geschlossen");
		$this->RegisterPropertyInteger("SoundID", 0);
		
		// Profile erstellen
		$this->RegisterProfileInteger("IPS2LOGO.RaffstoreState", "Information", "", "", 0, 3, 1);
		IPS_SetVariableProfileAssociation("IPS2LOGO.RaffstoreState", 0, "Geöffnet", "Raffstore", 0xFF0000);
		IPS_SetVariableProfileAssociation("IPS2LOGO.RaffstoreState", 50, "Undefiniert", "Raffstore", 0x0000FF);
		IPS_SetVariableProfileAssociation("IPS2LOGO.RaffstoreState", 100, "Geschlossen", "Raffstore", 0x00FF00);
		
		//Status-Variablen anlegen
		$this->RegisterVariableInteger("State", "State", "~ShutterMoveStop", 10);
		$this->EnableAction("State");
		
		$this->RegisterVariableInteger("GateState", "GateState", "IPS2LOGO.RaffstoreState", 20);
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
		$arrayElements[] = array("type" => "Select", "name" => "Model", "caption" => "Modell", "options" => $arrayOptions );
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "label" => "Auswahl des Netzwerkeingangs Öffnen");
		
		$arrayOptions = array();
		for ($i = 0; $i <= 7; $i++) {
			$arrayOptions[] = array("label" => $i, "value" => $i);
		}
		
		$ArrayRowLayout = array();
		$ArrayRowLayout[] = array("type" => "Select", "name" => "Address", "caption" => "Adresse_1", "options" => $arrayOptions );
		$ArrayRowLayout[] = array("type" => "Select", "name" => "Bit", "caption" => "Bit_1", "options" => $arrayOptions );
		$arrayElements[] = array("type" => "RowLayout", "items" => $ArrayRowLayout);

		$arrayElements[] = array("type" => "Label", "label" => "Auswahl des Netzwerkeingangs Schliessen"); 
		
		$ArrayRowLayout = array();
		$ArrayRowLayout[] = array("type" => "Select", "name" => "Address_2", "caption" => "Adresse", "options" => $arrayOptions );
		$ArrayRowLayout[] = array("type" => "Select", "name" => "Bit_2", "caption" => "Bit", "options" => $arrayOptions );
		$arrayElements[] = array("type" => "RowLayout", "items" => $ArrayRowLayout);
		
		$arrayElements[] = array("type" => "Label", "label" => "Laufzeit des Rolladen"); 
		$arrayElements[] = array("type" => "IntervalBox", "name" => "Timer_1", "caption" => "s");
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "label" => "Variable die den aktuellen Temperaturwert enthält:");
		$arrayElements[] = array("type" => "SelectVariable", "name" => "ActualTemperatureID", "caption" => "Ist-Temperatur"); 
 		$arrayElements[] = array("type" => "Label", "label" => "Mindesttemperatur für den Betrieb:");
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Temperature", "caption" => "Temperatur", "digits" => 1);
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "label" => "Benachrichtigungsfunktion");
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
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arrayActions = array(); 
		$arrayActions[] = array("type" => "Label", "label" => "Test Center"); 
		$arrayActions[] = array("type" => "TestCenter", "name" => "TestCenter");
		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements, "actions" => $arrayActions)); 	
 	} 
	
	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
                // Diese Zeile nicht löschen
                parent::ApplyChanges();
		
		// Registrierung für die Änderung der Ist-Temperatur
		If ($this->ReadPropertyInteger("ActualTemperatureID") > 0) {
			$this->RegisterMessage($this->ReadPropertyInteger("ActualTemperatureID"), 10603);
		}
		
		SetValueInteger($this->GetIDForIdent("State"), 2);
		
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->SetStatus(102);
		}
		else {
			$this->SetStatus(104);
		}
		
	}
	
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    	{
		switch ($Message) {
			case 10603:
				// Änderung der Ist-Temperatur, die Temperatur aus dem angegebenen Sensor in das Modul kopieren
				If ($SenderID == $this->ReadPropertyInteger("ActualTemperatureID")) {
					$ActualTemperature = GetValueFloat($this->ReadPropertyInteger("ActualTemperatureID"));
					$Temperature = $this->ReadPropertyFloat("Temperature");
					If ($ActualTemperature >= $Temperature) {
						$this->EnableAction("State");
					}
					else {
						$this->DisableAction("State");
					}
				}
				break;

		}
    	}
	
	public function RequestAction($Ident, $Value) 
	{
  		switch($Ident) {
	        case "State":
			If ($Value <> GetValueInteger($this->GetIDForIdent("State"))) {
				$this->KeyPress($Value);
			}
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
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {
			$this->SendDebug("SetState", "Ausfuehrung", 0);
			$Area = 132; // Konstante
			If ($Button == 0) {
				$Address = $this->ReadPropertyInteger("Address_1"); // Öffnen
				$Bit = $this->ReadPropertyInteger("Bit_1");
			}
			elseIf ($Button == 4) {
				$Address = $this->ReadPropertyInteger("Address_2"); // Schliessen
				$Bit = $this->ReadPropertyInteger("Bit_2");
			}
			//$AddressBit = ($Address * 10) + $Bit;
			//$AddressBit = intval(octdec($AddressBit));
			
			$AddressBit = ($Address * 8) + $Bit;
			$AddressBit = intval($AddressBit);

			
			If ($State == true) {
				$DataPayload = utf8_encode(chr(1));
			}
			else {
				$DataPayload = utf8_encode(chr(0));
			}
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}", "Function" => 5, "Area" => $Area, "AreaAddress" => 0, "BitAddress" => $AddressBit, "WordLength" => 1,"DataCount" => 1,"DataPayload" => $DataPayload)));

			//$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}", "Function" => 5, "Area" => $Area, "AreaAddress" => $Address[$Button], "BitAddress" => $Bit[$Button], "WordLength" => 1,"DataCount" => 1,"DataPayload" => $DataPayload)));
			$this->SendDebug("SetState", "Ergebnis: ".intval($Result), 0);
		}
	}
	   
	public function Keypress(Int $Button)
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {	
			SetValueInteger($this->GetIDForIdent("State"), $Button);
			$this->SetState(false, 0);
			$this->SetState(false, 4);
			If ($Button == 2) {
				$this->SetTimerInterval("Timer_1", 0);
			}
			else {
				$this->SetTimerInterval("Timer_1", $this->ReadPropertyInteger("Timer_1") * 1000);
				SetValueInteger($this->GetIDForIdent("GateState"), 50);
				$this->SetState(true, $Button);
				$this->SetBuffer("Button", $Button);
			}
		}
  	}
	    
	public function StateReset()
	{
		$Button = $this->GetBuffer("Button");
		$this->SetState(false, $Button);
		SetValueInteger($this->GetIDForIdent("State"), 2);
		$this->SetTimerInterval("Timer_1", 0);
		If ($Button == 0) {
			SetValueInteger($this->GetIDForIdent("GateState"), 0);
			$this->Notification($this->ReadPropertyString("TextOpen"));
		}
		elseif ($Button == 4) {
			SetValueInteger($this->GetIDForIdent("GateState"), 100);
			$this->Notification($this->ReadPropertyString("TextClose"));
		}
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
