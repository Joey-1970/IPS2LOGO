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
		$this->RegisterPropertyInteger("Timer_1", 30);
		$this->RegisterTimer("Timer_1", 0, 'I2LRolladen_StateReset($_IPS["TARGET"]);');
		$this->RegisterPropertyInteger("ActualTemperatureID", 0);
		$this->RegisterPropertyFloat("Temperature", 3.0);
		
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
		$arrayElements[] = array("type" => "Label", "label" => "Laufzeit des Tast-Impulses");
		$arrayElements[] = array("type" => "IntervalBox", "name" => "Switchtime", "caption" => "ms");
		$arrayElements[] = array("type" => "Label", "label" => "Laufzeit des Rolladen"); 
		$arrayElements[] = array("type" => "IntervalBox", "name" => "Timer_1", "caption" => "s");
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "label" => "Variable die den aktuellen Temperaturwert enthält:");
		$arrayElements[] = array("type" => "SelectVariable", "name" => "ActualTemperatureID", "caption" => "Ist-Temperatur"); 
 		$arrayElements[] = array("type" => "Label", "label" => "Mindesttemperatur für den Betrieb:");
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Temperature", "caption" => "Temperatur", "digits" => 1);
		
		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
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
			$Address[0] = $this->ReadPropertyInteger("Address_1"); // Öffnen
			$Bit[0] = $this->ReadPropertyInteger("Bit_1");
			$Address[4] = $this->ReadPropertyInteger("Address_2"); // Schliessen
			$Bit[4] = $this->ReadPropertyInteger("Bit_2");
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
			SetValueInteger($this->GetIDForIdent("State"), $Button);
			$this->SetState(false, 0);
			$this->SetState(false, 4);
			If ($Button == 2) {
				$this->SetTimerInterval("Timer_1", 0);
			}
			else {
				$this->SetTimerInterval("Timer_1", $this->ReadPropertyInteger("Timer_1") * 1000);
				SetValueInteger($this->GetIDForIdent("GateState"), 25);
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
		}
		elseif ($Button == 4) {
			SetValueInteger($this->GetIDForIdent("GateState"), 100);
		}
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
