<?
    // Klassendefinition
    class IPS2LOGO_Zirkulation extends IPSModule 
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
		$this->RegisterPropertyInteger("Output", 1);
		$this->RegisterPropertyInteger("Address", 0);
		$this->RegisterPropertyInteger("Bit", 0);
		$this->RegisterPropertyInteger("Switchtime", 20);
		$this->RegisterPropertyInteger("Timer_1", 250);
		$this->RegisterTimer("Timer_1", 0, 'I2LZirkulation_GetState($_IPS["TARGET"]);');
		
		$this->RegisterPropertyInteger("FlowTemperature_ID", 0);
		$this->RegisterPropertyInteger("ReturnTemperature_ID", 0);
		$this->RegisterPropertyInteger("Amplification", 10);
		$this->RegisterPropertyInteger("PitchThreshold", 2);
		$this->RegisterPropertyInteger("MinRuntime", 120);
		$this->RegisterPropertyInteger("ParallelShift", 15);
		
		//Status-Variablen anlegen
		$this->RegisterVariableBoolean("State", "State", "~Switch", 10);		
	
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
		$arrayElements[] = array("type" => "Label", "label" => "Auswahl des Netzwerkeingangs"); 
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Address",  "caption" => "Adresse"); 
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Bit",  "caption" => "Bit"); 
		$arrayElements[] = array("type" => "Label", "label" => "Laufzeit des Tast-Impulses");
		$arrayElements[] = array("type" => "IntervalBox", "name" => "Switchtime", "caption" => "ms");
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________"); 
		$arrayElements[] = array("type" => "Label", "label" => "Auswahl des digitalen Ausgangs oder Merkers"); 
		$arrayOptions = array();
		for ($i = 1; $i <= 16; $i++) {
		    	$arrayOptions[] = array("label" => "Q".$i, "value" => $i);
		}
		for ($i = 1; $i <= 27; $i++) {
		    	$arrayOptions[] = array("label" => "M".$i, "value" => ($i + 100));
		}
		$arrayElements[] = array("type" => "Select", "name" => "Output", "caption" => "Ausgang", "options" => $arrayOptions );
		$arrayElements[] = array("type" => "IntervalBox", "name" => "Timer_1", "caption" => "ms");
		$arrayElements[] = array("type" => "Label", "label" => "_____________________________________________________________________________________________________"); 
		$arrayElements[] = array("type" => "Label", "label" => "Variable der Vorlauftemperatur");
		$arrayElements[] = array("type" => "SelectVariable", "name" => "FlowTemperature_ID", "caption" => "Variablen ID");
		$arrayElements[] = array("type" => "Label", "label" => "Variable der Rücklauftemperatur");
		$arrayElements[] = array("type" => "SelectVariable", "name" => "ReturnTemperature_ID", "caption" => "Variablen ID");
		$arrayElements[] = array("type" => "Label", "label" => "Verstärkungsfaktor der Temperaturdifferenz");
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "Amplification", "caption" => "Faktor");
		$arrayElements[] = array("type" => "Label", "label" => "Schwellwert der Steigung");
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "PitchThreshold", "caption" => "Schwellwert", "digits" => 1);
		$arrayElements[] = array("type" => "Label", "label" => "Minimale Laufzeit der Zirkulationspumpe");
		$arrayElements[] = array("type" => "IntervalBox", "name" => "MinRuntime", "caption" => "Sekunden");
		$arrayElements[] = array("type" => "Label", "label" => "Temperaturdifferenz Vor- zu Rücklauf als Abschaltbedingung (K)");
		$arrayElements[] = array("type" => "NumberSpinner", "name" => "ParallelShift", "caption" => "Temperaturdifferenz");

		
		return JSON_encode(array("status" => $arrayStatus, "elements" => $arrayElements)); 		 
 	} 
	
	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() 
        {
                // Diese Zeile nicht löschen
                parent::ApplyChanges();
		
		// Anlegen des Wochenplans
		$this->RegisterEvent("Wochenplan", "IPS2Cn_Event_".$this->InstanceID, 2, $this->InstanceID, 20);
		
		// Anlegen der Daten für den Wochenplan
		for ($i = 0; $i <= 6; $i++) {
			IPS_SetEventScheduleGroup($this->GetIDForIdent("IPS2Cn_Event_".$this->InstanceID), $i, pow(2, $i));
		}
		
		$this->RegisterScheduleAction($this->GetIDForIdent("IPS2Cn_Event_".$this->InstanceID), 0, "An", 0x40FF00, "IPS2Cn_SetPumpState(\$_IPS['TARGET'], 1);");
		$this->RegisterScheduleAction($this->GetIDForIdent("IPS2Cn_Event_".$this->InstanceID), 1, "Aus", 0xFF0040, "IPS2Cn_SetPumpState(\$_IPS['TARGET'], 0);");
		
		
		// Registrierung für die Änderung der Vorlauf-Temperatur
		If ($this->ReadPropertyInteger("FlowTemperature_ID") > 0) {
			$this->RegisterMessage($this->ReadPropertyInteger("FlowTemperature_ID"), 10603);
		}
		// Registrierung für die Änderung der Return-Temperatur
		If ($this->ReadPropertyInteger("ReturnTemperature_ID") > 0) {
			$this->RegisterMessage($this->ReadPropertyInteger("ReturnTemperature_ID"), 10603);
		}
		// Registrierung für den Wochenplan
		$this->RegisterMessage($this->GetIDForIdent("IPS2Cn_Event_".$this->InstanceID), 10803);
	
		
		
		If ($this->ReadPropertyBoolean("Open") == true) {
			$this->GetState();
			$this->SetStatus(102);
			$this->SetTimerInterval("Timer_1", $this->ReadPropertyInteger("Timer_1") );
		}
		else {
			$this->SetStatus(104);
			$this->SetTimerInterval("Timer_1", 0);
		}
		
	}
	
	public function RequestAction($Ident, $Value) 
	{
  		switch($Ident) {
	       
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
	    
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    	{
		switch ($Message) {
			case 10803:
				$this->SendDebug("ReceiveData", "Ausloeser Wochenplan", 0);
				break;
			case 10603:
				// Änderung der Vorlauf-Temperatur
				If ($SenderID == $this->ReadPropertyInteger("FlowTemperature_ID")) {
					$this->SendDebug("ReceiveData", "Ausloeser Aenderung Vorlauf-Temperatur", 0);
					$this->Calculate();
				}
				//Änderung der Rücklauf-Temperatur
				elseif ($SenderID == $this->ReadPropertyInteger("ReturnTemperature_ID")) {
					$this->SendDebug("ReceiveData", "Ausloeser Aenderung Ruecklauf-Temperatur", 0);
					$this->SwitchOff();
				}
				break;
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
			$AddressBit = ($Address * 10) + $Bit;
			$AddressBit = intval(octdec($AddressBit));
			If ($State == true) {
				$DataPayload = utf8_encode(chr(1));
			}
			else {
				$DataPayload = utf8_encode(chr(0));
			}
			$Result = $this->SendDataToParent(json_encode(Array("DataID"=> "{042EF3A2-ECF4-404B-9FA2-42BA032F4A56}", "Function" => 5, "Area" => $Area, "AreaAddress" => 0, "BitAddress" => $AddressBit, "WordLength" => 1,"DataCount" => 1,"DataPayload" => $DataPayload)));
			//$this->SendDebug("SetState", "Ergebnis: ".intval($Result), 0);
		}
	}
	    
	public function GetState()
	{
		If (($this->ReadPropertyBoolean("Open") == true) AND ($this->HasActiveParent() == true)) {
			$this->SendDebug("GetState", "Ausfuehrung", 0);
			$Output = $this->ReadPropertyInteger("Output");
			$AreaAddress = 0;
			
			If ($Output < 100) {
				$Area = 130; // Ausgang
				$BitAddress = $Output - 1;
			}
			else {
				$Area = 131; // Merker
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
				If ($State <> GetValueBoolean($this->GetIDForIdent("State"))) {
					SetValueBoolean($this->GetIDForIdent("State"), $State);
				}
			}
		}
	}
	    
	public function Calculate()
	{
		// Prüfen, ob die Zirkulationspumpe aufgrund einer Warmwasseranforderung eingeschaltet werden soll
		If (($this->ReadPropertyInteger("FlowTemperature_ID") > 0) AND ($this->ReadPropertyInteger("ReturnTemperature_ID"))) {
			$FlowTemperature = GetValueFloat($this->ReadPropertyInteger("FlowTemperature_ID"));
			$TempDiff = $FlowTemperature - floatval($this->GetBuffer("LastFlowTemperature"));
			$TimeDiff = time() -  intval($this->GetBuffer("LastCalculate"));
			$Amplification = $this->ReadPropertyInteger("Amplification");
			$PumpState = GetValueBoolean($this->GetIDForIdent("State"));
			$PitchThreshold = $this->ReadPropertyInteger("PitchThreshold");
			
			If ($TimeDiff > 0) {
				$Pitch = ($TempDiff * $Amplification) / $TimeDiff;
				$this->SendDebug("Calculate", "Steigung: ".round($Pitch, 2)." Temperaturdifferenz: ".$TempDiff." °C Zeitdifferenz: ".round($TimeDiff, 2), 0);
				If (($Pitch > $PitchThreshold) And ($TimeDiff > 1) And ($PumpState == false)) {
					// Pumpe einschalten
					$this->SetState(true);
					$this->SendDebug("Calculate", "Die Zirkulationspumpe wird wegen der Warmwasseranforderung eingeschaltet", 0);
					$this->SetBuffer("LastSwitchOn", time());
				}
			}
			
			$this->SetBuffer("LastCalculate", time());
			$this->SetBuffer("LastFlowTemperature", $FlowTemperature);
		}
			
	}
	
	public function SetPumpState(string $State)
	{
		$this->SendDebug("SetPumpState", "Aufruf aus dem Wochenplan", 0);
	}
	    
	private function SwitchOff()
	{
		If (($this->ReadPropertyInteger("FlowTemperature_ID") > 0) AND ($this->ReadPropertyInteger("ReturnTemperature_ID"))) {
			$FlowTemperature = GetValueFloat($this->ReadPropertyInteger("FlowTemperature_ID"));
			$ReturnTemperature = GetValueFloat($this->ReadPropertyInteger("ReturnTemperature_ID"));
			$TempDiff = $FlowTemperature - $ReturnTemperature;
			$TimeDiff = time() - intval($this->GetBuffer("LastSwitchOn"));
			$MinRuntime = $this->ReadPropertyInteger("MinRuntime");
			$ParallelShift = $this->ReadPropertyInteger("ParallelShift");
			$PumpState = GetValueBoolean($this->GetIDForIdent("State"));
			
			If ($TimeDiff > $MinRuntime) {
				$this->SendDebug("SwitchOff", "TimeDiff: ".($TimeDiff - $MinRuntime), 0);
				If (($ReturnTemperature + $ParallelShift) > $FlowTemperature) {
					$this->SendDebug("SwitchOff", "TempDiff: ".(($ReturnTemperature + $ParallelShift) - $FlowTemperature), 0);
					If ($PumpState == true) {
						$this->SendDebug("SwitchOff", "PumpState:".$PumpState, 0);
						// Pumpe ausschalten
						$this->SetState(false);
						$this->SendDebug("SwitchOff", "Die Zirkulationspumpe wird ausgeschaltet da der Schwellwert der Rücklauftemperatur erreicht wurde", 0);
					}
				}
			}
			else {
				$this->SendDebug("SwitchOff", "Die Zirkulationspumpe wird nicht ausgeschaltet.", 0);
				$this->SendDebug("SwitchOff", "TimeDiff: ".$TimeDiff." MinRuntime: ".$MinRuntime , 0);
				$this->SendDebug("SwitchOff", "ReturnTemperature: ".$ReturnTemperature." FlowTemperature: ".$FlowTemperature , 0);
			}
		}
	}
	    
	private function GetEventActionID($EventID, $EventType, $Days, $Hour, $Minute)
	{
		$EventValue = IPS_GetEvent($EventID);
		$Result = false;
		// Prüfen um welche Art von Event es sich handelt
		If ($EventValue['EventType'] == $EventType) {
			$ScheduleGroups = $EventValue['ScheduleGroups'];
			// Anzahl der ScheduleGroups ermitteln	
			$ScheduleGroupsCount = count($ScheduleGroups);
			If ($ScheduleGroupsCount > 0) {
				for ($i = 0; $i <= $ScheduleGroupsCount - 1; $i++) {	
					If ($ScheduleGroups[$i]['Days'] == $Days) {
						$ScheduleGroupDay = $ScheduleGroups[$i];
						$ScheduleGroupsDayCount = count($ScheduleGroupDay['Points']);
						If ($ScheduleGroupsDayCount == 0) {
							IPS_LogMessage("IPS2SingleRoomControl", "Keine Schaltpunkte definiert!"); 	
						}
						elseif ($ScheduleGroupsDayCount == 1) {
							$Result = $ScheduleGroupDay['Points'][0]['ActionID'];
						}
						elseif ($ScheduleGroupsDayCount > 1) {
							for ($j = 0; $j <= $ScheduleGroupsDayCount - 1; $j++) {
								$TimestampScheduleStart = mktime($ScheduleGroupDay['Points'][$j]['Start']['Hour'], $ScheduleGroupDay['Points'][$j]['Start']['Minute'], 0, 0, 0, 0);
								If ($j < $ScheduleGroupsDayCount - 1) {
									$TimestampScheduleEnd = mktime($ScheduleGroupDay['Points'][$j + 1]['Start']['Hour'], $ScheduleGroupDay['Points'][$j + 1]['Start']['Minute'], 0, 0, 0, 0);
								}
								else {
									$TimestampScheduleEnd = mktime(24, 0, 0, 0, 0, 0);
								}
								$Timestamp = mktime($Hour, $Minute, 0, 0, 0, 0);
								If (($Timestamp >= $TimestampScheduleStart) AND ($Timestamp < $TimestampScheduleEnd)) {
									$Result = ($ScheduleGroupDay['Points'][$j]['ActionID']) + 1;
								} 
							}
						}
					}
				}
			}
			else {
				IPS_LogMessage("IPS2SingleRoomControl", "Es sind keine Aktionen eingerichtet!");
			}
		  }
	return $Result;
	}
	
	private function RegisterEvent($Name, $Ident, $Typ, $Parent, $Position)
	{
		$eid = @$this->GetIDForIdent($Ident);
		if($eid === false) {
		    	$eid = 0;
		} elseif(IPS_GetEvent($eid)['EventType'] <> $Typ) {
		    	IPS_DeleteEvent($eid);
		    	$eid = 0;
		}
		//we need to create one
		if ($eid == 0) {
			$EventID = IPS_CreateEvent($Typ);
		    	IPS_SetParent($EventID, $Parent);
		    	IPS_SetIdent($EventID, $Ident);
		    	IPS_SetName($EventID, $Name);
		    	IPS_SetPosition($EventID, $Position);
		    	IPS_SetEventActive($EventID, true);  
		}
	}  
	
	private function RegisterScheduleAction($EventID, $ActionID, $Name, $Color, $Script)
	{
		IPS_SetEventScheduleAction($EventID, $ActionID, $Name, $Color, $Script);
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
