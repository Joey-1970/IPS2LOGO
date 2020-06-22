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
		for ($i = 0; $i <= 15; $i++) {
			$this->RegisterVariableBoolean("Output_".$i, "Ausgang Q".$i, "~Switch", ($i + 1) * 10);	
		}

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
		$arrayElements[] = array("type" => "IntervalBox", "name" => "Timer_1", "caption" => "ms");
		
		
		
 		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	} 
	
	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
                // Diese Zeile nicht löschen
                parent::ApplyChanges();
		
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
			$Area = 130;
			$AreaAddress = 742;
			$BitAddress = 0;
			// 22.06.2020, 15:30:53 |          ForwardData | Daten: {"DataID":"{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}","Function":4,"Area":130,"AreaAddress":742,"BitAddress":0,"WordLength":4,"DataCount":1,"DataPayload":""}

			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}", "Function" => 4, "Area" => $Area, "AreaAddress" => $AreaAddress, "BitAddress" => $BitAddress, "WordLength" => 4, "DataCount" => 1,"DataPayload" => "")));
			If ($Result === false) {
				$this->SetStatus(202);
				$this->SendDebug("GetState", "Fehler bei der Ausführung!", 0);
			}
			else {
				$this->SetStatus(102);
				$State = ($Result);
				$this->SendDebug("GetState", "Ergebnis: ".$State, 0);
				/*
				for ($i = 0; $i <= 15; $i++) {
					$Bitvalue = boolval($State & pow(2, $i));					
					If (GetValueBoolean($this->GetIDForIdent("Output_".$i)) <> $Bitvalue) {
						SetValueBoolean($this->GetIDForIdent("Output_".$i), $Bitvalue);
					}
				}
				*/
				
			}
			
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
