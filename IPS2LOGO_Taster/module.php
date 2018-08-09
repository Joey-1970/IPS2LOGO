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
		$arrayElements[] = array("type" => "SelectVariable", "name" => "State_ID", "caption" => "Variablen ID");
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
	// Netzwerkbefehl ein
	    // Daten: {"DataID":"{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}","Function":5,"Area":132,"AreaAddress":0,"BitAddress":0,"WordLength":1,"DataCount":1,"DataPayload":"\u0001"}
	    // aus
	    // Daten: {"DataID":"{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}","Function":5,"Area":132,"AreaAddress":0,"BitAddress":0,"WordLength":1,"DataCount":1,"DataPayload":"\u0000"}
	public function S7_WriteBit(Bool $State)
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {
			$this->SendDebug("S7_WriteBit", "Ausfuehrung", 0);
			If ($State = true) {
				$DataPayload = "\u0001";
			}
			else {
				$DataPayload = "\u0000";
			}
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}", "Function" => 5, "Area" => 132, "AreaAddress" => 0, "BitAddress" => 0, "WordLength" => 1,"DataCount" => 1,"DataPayload" => $DataPayload)));
			$this->SendDebug("S7_WriteBit", "Ergebnis: ".$Result, 0);
		}
	}
	    
	public function Keypress()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {	
			$SwitchID = $this->ReadPropertyInteger("Switch_ID"); // Instanz des Netzwerkeingangs
			$Switchtime = $this->ReadPropertyInteger("Switchtime"); // Dauer der Betätigung

			//$result = @S7_WriteBit($SwitchID, true);
			$result = $this->S7_WriteBit(true);
			if ($result==false)
			{
				$this->LogoReset();
				//S7_WriteBit($SwitchID , true);
				
			}
			IPS_Sleep($Switchtime);
			//S7_WriteBit($SwitchID ,false);
			$result = $this->S7_WriteBit(false);
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
