<?
    // Klassendefinition
    class IPS2LOGO_Rolladen extends IPSModule 
    {
	public function Destroy() 
	{
		//Never delete this line!
		parent::Destroy();
		$this->SetTimerInterval("Timer_1", 0);
	}
	    
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->ConnectParent("{1B0A36F7-343F-42F3-8181-0748819FB324}");
            	$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("Address_1", 0);
		$this->RegisterPropertyInteger("Bit_1", 0);
		$this->RegisterPropertyInteger("Address_2", 0);
		$this->RegisterPropertyInteger("Bit_2", 0);
		$this->RegisterPropertyInteger("Address_3", 0);
		$this->RegisterPropertyInteger("Bit_3", 0);
		$this->RegisterPropertyInteger("Switchtime", 20);
		$this->RegisterPropertyInteger("Timer_1", 30);
		$this->RegisterTimer("Timer_1", 0, 'I2LRolladen_StateReset($_IPS["TARGET"]);');
		
		//Status-Variablen anlegen
		$this->RegisterVariableInteger("State", "State", "~ShutterMoveStop", 10);
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
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "label" => "Auswahl des Netzwerkeingangs Öffnen"); 
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Address_1",  "caption" => "Adresse"); 
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Bit_1",  "caption" => "Bit"); 
		$arrayElements[] = array("type" => "Label", "label" => "Auswahl des Netzwerkeingangs Stoppen"); 
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Address_2",  "caption" => "Adresse"); 
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Bit_2",  "caption" => "Bit"); 
		$arrayElements[] = array("type" => "Label", "label" => "Auswahl des Netzwerkeingangs Schliessen"); 
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Address_3",  "caption" => "Adresse"); 
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Bit_3",  "caption" => "Bit"); 
		$arrayElements[] = array("type" => "Label", "label" => "Laufzeit des Tast-Impulses");
		$arrayElements[] = array("type" => "IntervalBox", "name" => "Switchtime", "caption" => "ms");
		$arrayElements[] = array("type" => "Label", "label" => "Laufzeit des Rolladen"); 
		$arrayElements[] = array("type" => "IntervalBox", "name" => "Timer_1", "caption" => "s");
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	} 
	
	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
                // Diese Zeile nicht löschen
                parent::ApplyChanges();
		
		SetValueInteger($this->GetIDForIdent("State"), 2);
		
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->SetStatus(102);
		}
		else {
			$this->SetStatus(104);
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
			$Address[0] = $this->ReadPropertyInteger("Address_1"); // Öffnen
			$Bit[0] = $this->ReadPropertyInteger("Bit_1");
			$Address[2] = $this->ReadPropertyInteger("Address_2"); // Stop
			$Bit[2] = $this->ReadPropertyInteger("Bit_2");
			$Address[4] = $this->ReadPropertyInteger("Address_3"); // Schliessen
			$Bit[4] = $this->ReadPropertyInteger("Bit_3");
			If ($State == true) {
				$DataPayload = utf8_encode(chr(1));
			}
			else {
				$DataPayload = utf8_encode(chr(0));
			}
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}", "Function" => 5, "Area" => $Area, "AreaAddress" => $Address[$Button], "BitAddress" => $Bit[$Button], "WordLength" => 1,"DataCount" => 1,"DataPayload" => $DataPayload)));
			$this->SendDebug("SetState", "Ergebnis: ".intval($Result), 0);
		}
	}
	        
	public function Keypress(Int $Button)
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {	
			$Switchtime = $this->ReadPropertyInteger("Switchtime"); // Dauer der Betätigung
			SetValueInteger($this->GetIDForIdent("State"), $Button);
			$this->SetState(true, $Button);
			IPS_Sleep($Switchtime);
			$this->SetState(false, $Button);
			$this->SetTimerInterval("Timer_1", $this->ReadPropertyInteger("Timer_1") * 1000);
		}
  	}
	    
	public function StateReset()
	{
		$this->SetTimerInterval("Timer_1", 0);
		SetValueInteger($this->GetIDForIdent("State"), 2);
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
	    
	private function HasActiveParent()
    	{
		$Instance = @IPS_GetInstance($this->InstanceID);
		if ($Instance['ConnectionID'] > 0)
		{
			$Parent = IPS_GetInstance($Instance['ConnectionID']);
			if ($Parent['InstanceStatus'] == 102)
			return true;
		}
        return false;
    	}  
}
?>
