<?
    // Klassendefinition
    class IPS2LOGO_Tueroeffner extends IPSModule 
    {
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->ConnectParent("{1B0A36F7-343F-42F3-8181-0748819FB324}");
            	$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("Model", 7);
		$this->RegisterPropertyInteger("Address", 0);
		$this->RegisterPropertyInteger("Bit", 0);
		$this->RegisterPropertyInteger("Timer_1", 5);
		$this->RegisterTimer("Timer_1", 0, 'I2LTueroeffner_StateReset($_IPS["TARGET"]);');
		$this->RegisterPropertyInteger("WebfrontID", 0);
		$this->RegisterPropertyString("Title", "Meldungstitel");
		$this->RegisterPropertyString("TextOpen", "Text geöffnet");
		$this->RegisterPropertyInteger("SoundID", 0);
		
		//Status-Variablen anlegen
		$this->RegisterVariableBoolean("State", "State", "~Lock.Reversed", 10);
		$this->EnableAction("State");
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
		
		$arrayElements[] = array("type" => "Label", "label" => "Laufzeit des Impulses");
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Timer_1", "caption" => "s", "minumum" => 0);
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
		$arrayOptions = array();
		$SoundArray = array("Alarm", "Bell", "Boom", "Buzzer", "Connected", "Dark", "Digital", "Drums", "Duck", "Full", "Happy", "Horn", "Inception", "Kazoo", "Roll", "Siren", "Space", "Trickling", "Turn");
		foreach ($SoundArray as $ID => $Sound) {
        		$arrayOptions[] = array("label" => $Sound, "value" => $ID);
    		}
		$arrayElements[] = array("type" => "Select", "name" => "SoundID", "caption" => "Sound", "options" => $arrayOptions );		
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
		
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->SetStatus(102);
		}
		else {
			$this->SetStatus(104);
			$this->SetTimerInterval("Timer_1", 0);
		}
		
	}
	
	public function RequestAction($Ident, $Value) 
	{
  		switch($Ident) {
	        case "State":
			If (($Value <> GetValueBoolean($this->GetIDForIdent("State"))) AND ($Value == true)) {
				$this->KeyPress();
			}
	            break;
	        default:
	            throw new Exception("Invalid Ident");
	    	}
	}

	// Beginn der Funktionen
	public function SetState(Bool $State)
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {
			$this->SendDebug("SetState", "Ausfuehrung", 0);
			$Area = 132; // Konstante
			$Address = $this->ReadPropertyInteger("Address"); 
			$Bit = $this->ReadPropertyInteger("Bit");
			
			If ($State == true) {
				$DataPayload = utf8_encode(chr(1));
			}
			else {
				$DataPayload = utf8_encode(chr(0));
			}
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}", "Function" => 5, "Area" => $Area, "AreaAddress" => $Address, "BitAddress" => $Bit, "WordLength" => 1,"DataCount" => 1,"DataPayload" => $DataPayload)));
			$this->SendDebug("SetState", "Ergebnis: ".intval($Result), 0);
		}
	}
	        
	public function Keypress()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {	
			SetValueBoolean($this->GetIDForIdent("State"), true);
			$this->Notification($this->ReadPropertyString("TextOpen"));
			$this->SetTimerInterval("Timer_1", $this->ReadPropertyInteger("Timer_1") * 1000);
			$this->SetState(true);
		}
  	}
	    
	public function StateReset()
	{
		$this->SetState(false);
		$this->SetTimerInterval("Timer_1", 0);
		SetValueBoolean($this->GetIDForIdent("State"), false);
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
}
?>
