<?
    // Klassendefinition
    class IPS2LOGO_Garagentor extends IPSModule 
    {
	public function Destroy() 
	{
		//Never delete this line!
		parent::Destroy();
		$this->SetTimerInterval("Timer_1", 0);
		$this->SetTimerInterval("Timer_2", 0);
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
		$this->RegisterPropertyInteger("Output_1", 1);
		$this->RegisterPropertyInteger("Address_2", 0);
		$this->RegisterPropertyInteger("Bit_2", 0);
		$this->RegisterPropertyInteger("Output_2", 1);
		$this->RegisterPropertyInteger("Timer_1", 0); // Laufzeit des Tastsignals
		$this->RegisterTimer("Timer_1", 0, 'I2LGaragentor_StateReset($_IPS["TARGET"]);'); 
		$this->RegisterPropertyInteger("Timer_2", 250); // Abfrageintervall des Status
		$this->RegisterTimer("Timer_2", 0, 'I2LGaragentor_GetGateState($_IPS["TARGET"]);');
		$this->RegisterPropertyInteger("Switchtime", 1000);
		
		// Profile erstellen
		$this->RegisterProfileInteger("IPS2LOGO.GateState", "Information", "", "", 0, 3, 1);
		IPS_SetVariableProfileAssociation("IPS2LOGO.GateState", 0, "Geöffnet", "Garage", 0xFF0000);
		IPS_SetVariableProfileAssociation("IPS2LOGO.GateState", 25, "Undefiniert", "Garage", 0x0000FF);
		IPS_SetVariableProfileAssociation("IPS2LOGO.GateState", 100, "Geschlossen", "Garage", 0x00FF00);
		
		//Status-Variablen anlegen
		$this->RegisterVariableInteger("State", "State", "~ShutterMoveStop", 10);
		$this->EnableAction("State");
		
		$this->RegisterVariableInteger("GateState", "GateState", "IPS2LOGO.GateState", 20);

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
		$arrayElements[] = array("type" => "Label", "label" => "Auswahl des Netzwerkeingangs Schliessen"); 
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Address_2",  "caption" => "Adresse");
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Bit_2",  "caption" => "Bit"); 
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "label" => "Auswahl des digitalen Ausgangs oder Merkers - Tor ist auf"); 
		$arrayOptions = array();
		for ($i = 1; $i <= 16; $i++) {
		    	$arrayOptions[] = array("label" => "Q".$i, "value" => $i);
		}
		for ($i = 1; $i <= 27; $i++) {
		    	$arrayOptions[] = array("label" => "M".$i, "value" => ($i + 100));
		}
		$arrayElements[] = array("type" => "Select", "name" => "Output_1", "caption" => "Ausgang", "options" => $arrayOptions );
		
		$arrayElements[] = array("type" => "Label", "label" => "Auswahl des digitalen Ausgangs oder Merkers - Tor ist zu"); 
		$arrayOptions = array();
		for ($i = 1; $i <= 16; $i++) {
		    	$arrayOptions[] = array("label" => "Q".$i, "value" => $i);
		}
		for ($i = 1; $i <= 27; $i++) {
		    	$arrayOptions[] = array("label" => "M".$i, "value" => ($i + 100));
		}
		$arrayElements[] = array("type" => "Select", "name" => "Output_2", "caption" => "Ausgang", "options" => $arrayOptions );
		$arrayElements[] = array("type" => "IntervalBox", "name" => "Timer_2", "caption" => "ms");
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");

		$arrayElements[] = array("type" => "Label", "label" => "Laufzeit des Tast-Impulses");
		$arrayElements[] = array("type" => "IntervalBox", "name" => "Switchtime", "caption" => "ms");
		
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
			$Address[4] = $this->ReadPropertyInteger("Address_2"); // Schliessen
			$Bit[4] = $this->ReadPropertyInteger("Bit_2");
			$AddressBit = ($Address[$Button] * 10) + $Bit[$Button];
			$AddressBit = intval(octdec($AddressBit));
			If ($State == true) {
				$DataPayload = utf8_encode(chr(1));
			}
			else {
				$DataPayload = utf8_encode(chr(0));
			}
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}", "Function" => 5, "Area" => $Area, "AreaAddress" => 0, "BitAddress" => $AddressBit, "WordLength" => 1,"DataCount" => 1,"DataPayload" => $DataPayload)));
			$this->SendDebug("SetState", "Ergebnis: ".intval($Result), 0);
		}
	}
	        
	public function Keypress(Int $Button)
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {	
			SetValueInteger($this->GetIDForIdent("State"), $Button);
			If ($Button == 2) {
				$this->SetState(false, 0);
				$this->SetState(false, 4);
				$this->SetTimerInterval("Timer_1", 0);
			}
			else {
				$SwitchTime = $this->ReadPropertyInteger("Switchtime"); // Laufzeit des Signals
				$this->SetTimerInterval("Timer_1", $this->ReadPropertyInteger("Timer_1"));
				$this->SetState(true, $Button);
				$this->SetBuffer("Button", $Button);
			}
		}
  	}
	    
	public function StateReset()
	{
		$Button = $this->GetBuffer("Button");
		$this->SetState(false, $Button);
		$this->SetTimerInterval("Timer_1", 0);
	}
	
	public function GetGateState()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {
			$this->SendDebug("GetGateState", "Ausfuehrung", 0);
			$Output = $this->ReadPropertyInteger("Output_1");
			$StateTop = $this->GetState($Output);
			$Output = $this->ReadPropertyInteger("Output_2");
			$StateDown = $this->GetState($Output);
			If (($StateTop == true) AND ($StateDown == false)) {
				$State = 0;
			}
			elseIf (($StateTop == false) AND ($StateDown == true)) {
				$State = 100;
			}
			else {
				$State = 25;
			}
			If ($State <> GetValueInteger($this->GetIDForIdent("GateState"))) {
				SetValueInteger($this->GetIDForIdent("GateState"), $State);
			}
		}
	}
			
	    
	private function GetState(int $Output)
	{
		$this->SendDebug("GetState", "Ausfuehrung", 0);
		$Result = -1;

		If ($Output < 100) {
			$Area = 130; // Ausgang
			$AreaAddress = 0;
			//$AddressBit = ($Address * 10) + $Bit;
			//$BitAddress = intval(octdec($AddressBit));
			$BitAddress = $Output - 1;
		}
		else {
			$Area = 131; // Merker
			$Output = $Output - 100;
			If ($Output <= 8) {
				$AreaAddress = 0;
				$BitAddress = $Output - 1;
			}
			elseif (($Output > 8) AND ($Output <= 16)) {
				$AreaAddress = 1;
				$BitAddress = $Output - 9;
			}
			elseif (($Output > 16) AND ($Output <= 24)) {
				$AreaAddress = 2;
				$BitAddress = $Output - 17;
			}
			elseif ($Output > 24) {
				$AreaAddress = 3;
				$BitAddress = $Output - 25;
			}
		}

		$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}", "Function" => 4, "Area" => $Area, "AreaAddress" => $AreaAddress, "BitAddress" => $BitAddress, "WordLength" => 1, "DataCount" => 1,"DataPayload" => "")));
		If ($Result === false) {
			$this->SetStatus(202);
			$this->SendDebug("GetState", "Fehler bei der Ausführung!", 0);
		}
		else {
			$this->SetStatus(102);
			$State = ord($Result);
			$this->SendDebug("GetState", "Ergebnis: ".$State, 0);
			$Result = $State;
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
