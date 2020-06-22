<?
    // Klassendefinition
    class IPS2LOGO_Monitor extends IPSModule 
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
		$this->RegisterPropertyInteger("Model", 7);
		$this->RegisterPropertyInteger("Timer_1", 250); // Abfrageintervall des Status
		$this->RegisterTimer("Timer_1", 0, 'I2LMonitor_GetState($_IPS["TARGET"]);');
		
		//Status-Variablen anlegen
		

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

		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________");
		
		
		
		
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
			$this->GetState();
			$this->SetTimerInterval("Timer_1", $this->ReadPropertyInteger("Timer_1") );
		}
		else {
			$this->SetStatus(104);
			$this->SetTimerInterval("Timer_1", 0 );
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
	    
	public function ReceiveData($JSONString) 
	{
	    	// Empfangene Daten vom Gateway/Splitter
	    	$data = json_decode($JSONString);
	 	$this->SendDebug("ReceiveData", $data, 0);
 	}
	
	// Beginn der Funktionen
	
	        
	
	
	public function GetState()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {
			
			
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
        	//$this->UpdateFormField('Output_1', 'options', json_encode($arrayOptions));
		//$this->UpdateFormField('Output_2', 'options', json_encode($arrayOptions));
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
