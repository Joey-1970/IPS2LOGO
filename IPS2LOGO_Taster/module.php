<?
    // Klassendefinition
    class IPS2LOGO_Taster extends IPSModule 
    {
	public function Destroy() 
	{
		//Never delete this line!
		parent::Destroy();
		
	}
	    
	// Überschreibt die interne IPS_Create($id) Funktion
        public function Create() 
        {
            	// Diese Zeile nicht löschen.
            	parent::Create();
		$this->ConnectParent("{1B0A36F7-343F-42F3-8181-0748819FB324}");
            	$this->RegisterPropertyBoolean("Open", false);
		$this->RegisterPropertyInteger("State_ID", 0);
		$this->RegisterPropertyInteger("Switch_ID", 0);
		$this->RegisterPropertyInteger("Switchtime", 20);
		
		//Status-Variablen anlegen
		$this->RegisterVariableBoolean("State", "State", "~Switch", 10);
		$this->EnableAction("State");
        }
       	
	public function GetConfigurationForm() { 
		$arrayStatus = array(); 
		$arrayStatus[] = array("code" => 101, "icon" => "inactive", "caption" => "Instanz wird erstellt"); 
		$arrayStatus[] = array("code" => 102, "icon" => "active", "caption" => "Instanz ist aktiv");
		$arrayStatus[] = array("code" => 104, "icon" => "inactive", "caption" => "Instanz ist inaktiv");
		$arrayStatus[] = array("code" => 200, "icon" => "error", "caption" => "Instanz ist fehlerhaft"); 
		
		$arrayElements = array(); 
		$arrayElements[] = array("name" => "Open", "type" => "CheckBox",  "caption" => "Aktiv"); 
 		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________"); 
		$arrayElements[] = array("type" => "Label", "label" => "Status");
		$arrayElements[] = array("type" => "SelectVariable", "name" => "Status_ID", "caption" => "Variablen ID");
		$arrayElements[] = array("type" => "Label", "label" => "Schalter");
		$arrayElements[] = array("type" => "SelectVariable", "name" => "Switch_ID", "caption" => "Variablen ID");
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		$arrayElements[] = array("type" => "Label", "label" => "Laufzeit des Impulses");
		$arrayElements[] = array("type" => "IntervalBox", "name" => "Switchtime", "caption" => "ms");

		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	} 
	
	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
                // Diese Zeile nicht löschen
                parent::ApplyChanges();
		
		// Registrierung für die Änderung am LOGO-Status
		If ($this->ReadPropertyInteger("LOGO_ID") > 0) {
			//$this->RegisterMessage($this->ReadPropertyInteger("LOGO_ID"), 10603);
		}
		
		// Registrierung für die Änderung des Status
		If ($this->ReadPropertyInteger("State_ID") > 0) {
			$this->RegisterMessage($this->ReadPropertyInteger("Status_ID"), 10603);
		}
		
            	
		If (($this->ReadPropertyBoolean("Open") == true) AND  
			($this->ReadPropertyInteger("State_ID") > 0) AND 
			($this->ReadPropertyInteger("Switch_ID") > 0)) {
			SetValueBoolean($this->GetIDForIdent("State"), GetValueBoolean($this->ReadPropertyInteger("State_ID")));
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
	            If ($this->ReadPropertyBoolean("Open") == true) {
		    	//$this->Set_Status($Value);
		    }
	            break;
	        default:
	            throw new Exception("Invalid Ident");
	    	}
	}
	    
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    	{
		switch ($Message) {
			case 10803:
				//$this->SendDebug("ReceiveData", "Ausloeser Wochenplan", 0);
				break;
			case 10603:
				// Änderung der LOGO Status Variablen
				If ($SenderID == $this->ReadPropertyInteger("State_ID")) {
					$this->SendDebug("ReceiveData", "Ausloeser Aenderung Status", 0);
					SetValueBoolean($this->GetIDForIdent("State"), GetValueBoolean($this->ReadPropertyInteger("State_ID")));
				}
				
				break;
		}
    	}    
	
	// Beginn der Funktionen
	// Simuliert einen Tastfunktion auf der Logo
	public function Keypress()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {	
			$SwitchID = $this->ReadPropertyInteger("Switch_ID"); // Instanz des Netzwerkeingangs
			$Switchtime = $this->ReadPropertyInteger("Switchtime"); // Dauer der Betätigung

			$result = @S7_WriteBit($SwitchID, true);
			if ($result==false)
			{
				$this->LogoReset();
				S7_WriteBit($SwitchID , true);
			}
			IPS_Sleep($Switchtime);
			S7_WriteBit($SwitchID ,false);
		}
  	}
	    
	// Führt einen Reset der LOGO-Anbindung durch
	private function LogoReset()
	{
		$ParentID = $this->GetParentID();
		
		S7_SetOpen($ParentID, false);
		IPS_ApplyChanges($ParentID);
		IPS_Sleep(500);
		S7_SetOpen($ParentID, True);
		IPS_ApplyChanges($ParentID);
   	Return;
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
