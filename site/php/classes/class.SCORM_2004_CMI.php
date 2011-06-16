<?php
class SCORM_2004_CMI {
	private $student_id;
	private $sco_id;
	private $student_mode;
	
	function __construct($data) {
		$this->student_id   = $data->user_id; // Student ID
		$this->sco_id       = $data->sco_id;  // SCO ID
		$this->student_mode = $data->mode;    // Request
		
		/** Note to Developer
		* You will need to obtain the following from the student profile:
		* - Preferences, audio_level, language, delivery_speed, audio_captioning
		* Also you will need to put together the following:
		* - Learner Name
		* - Any launch data (global)
		* - Comments from LMS (maybe be added elsewhere)
		* - Mode may need to be dynamically set here on re-launch/launch
		* - More may bee salt and peppered below
		*/
		$cmi_path = "../data/" . $this->student_id . "_" . $this->sco_id . "_cmi.json"; 
		
		// Check to see if CMI object exists for student.. if so load it
		if(is_file($cmi_path)) {
			$fh = fopen($cmi_path, 'r') or $this->sendError("Can't open file");
			//$fh = file_get_contents($cmi_path);
			$this->response->data = json_decode(fread($fh, filesize($cmi_path)));			
			fclose($fh);
			
			// Make adjustments to the CMI as needed
			
			// Adjust Mode, but check if complete, or time expired later
			$this->response->data->cmi->mode = $this->student_mode;
			
			
			$this->response->format = "revisit";
			// End
			
		} else { // Create a CMI Object
		
		
			$this->response->data->cmi->_version                                        = "1.0";  // (characterstring, RO) Represents the version of the data model
			
			$this->response->data->cmi->comments_from_learner->_children                = "comment,location,timestamp";  
			                                                                             // (comment,location,timestamp, RO) Listing of supported data model elements
	 		$this->response->data->cmi->comments_from_learner->_count                   = "0"; // (non-negative integer, RO) Current number of learner comments 
	 		/*
	 		$this->response->data->cmi->comments_from_learner->n->comment               = "";  // (localized_string_type (SPM: 4000), RW) Textual input 
	 		$this->response->data->cmi->comments_from_learner->n->location              = "";  // (characterstring (SPM: 250), RW) Point in the SCO to which the comment applies 
	 		$this->response->data->cmi->comments_from_learner->n->timestamp             = "";  // (time (second,10,0), RW) Point in time at which the comment was created or most recently changed 
	 		*/
	 		$this->response->data->cmi->comments_from_lms->_children                    = "comment,location,timestamp";  
	 		                                                                             // (comment,location,timestamp, RO) Listing of supported data model elements 
	 		$this->response->data->cmi->comments_from_lms->_count                       = "0"; // (non-negative integer, RO) Current number of comments from the LMS 
	 		/*
	 		$this->response->data->cmi->comments_from_lms->n->comment                   = "";  // (localized_string_type (SPM: 4000), RO) Comments or annotations associated with a SCO 
	 		$this->response->data->cmi->comments_from_lms->n->location                  = "";  // (characterstring (SPM: 250), RO) Point in the SCO to which the comment applies 
	 		$this->response->data->cmi->comments_from_lms->n->timestamp                 = "";  // (time(second,10,0), RO) Point in time at which the comment was created or most recently changed 
	 		*/
	 		
	 		$this->response->data->cmi->completion_status                               = "not attempted";  // (state (completed, incomplete, not attempted, unknown), RW) Indicates whether the learner has completed the SCO 
	 		$this->response->data->cmi->completion_threshold                            = "";  // (real(10,7) range (0..1), RO) Used to determine whether the SCO should be considered complete 
	 		$this->response->data->cmi->credit                                          = "";  // (state (credit, no_credit), RO) Indicates whether the learner will be credited for performance in the SCO 
	 		$this->response->data->cmi->entry                                           = "";  // (state (ab_initio, resume, ""), RO) Asserts whether the learner has previously accessed the SCO 
	 		$this->response->data->cmi->exit                                            = "";  // (state (timeout, suspend, logout, normal, ""), W) Indicates how or why the learner left the SCO 
	 		
	 		$this->response->data->cmi->interactions->_children                         = "id,type,objectives,timestamp,correct_responses,weighting,learner_response,result,latency,description";  
	 		                                                                             // (id,type,objectives,timestamp,correct_responses,weighting,learner_response,result,latency,description, RO) Listing of supported data model elements
	 		$this->response->data->cmi->interactions->_count                            = "0"; // (non-negative integer, RO) Current number of interactions being stored by the LMS 
	 		/*
	 		$this->response->data->cmi->interactions->n->id                             = "";  // (long_identifier_type (SPM: 4000), RW) Unique label for the interaction 
	 		$this->response->data->cmi->interactions->n->type                           = "";  // (state (true_false, multiple_choice, fill_in, long_fill_in, matching, performance, sequencing, likert, numeric, other), RW) Which type of interaction is recorded 
	 		$this->response->data->cmi->interactions->n->objectives->_count             = "";  // (non-negative integer, RO) Current number of objectives (i.e., objective identifiers) being stored by the LMS for this interaction 
	 		$this->response->data->cmi->interactions->n->objectives->n->id              = "";  // (long_identifier_type (SPM: 4000), RW) Label for objectives associated with the interaction 
	 		$this->response->data->cmi->interactions->n->timestamp                      = "";  // (time(second,10,0), RW) Point in time at which the interaction was first made available to the learner for learner interaction and response
	 		$this->response->data->cmi->interactions->n->correct_responses->_count      = "0"; // (non-negative integer, RO) Current number of correct responses being stored by the LMS for this interaction 
	 		$this->response->data->cmi->interactions->n->correct_responses->n->pattern  = "";  // (format depends on interaction type, RW) One correct response pattern for the interaction 
	 		$this->response->data->cmi->interactions->n->weighting                      = "";  // (real (10,7), RW) Weight given to the interaction relative to other interactions 
	 		$this->response->data->cmi->interactions->n->learner_response               = "";  // (format depends on interaction type, RW) Data generated when a learner responds to an interaction 
	 		$this->response->data->cmi->interactions->n->result                         = "";  // (state (correct, incorrect, unanticipated, neutral, real (10,7) ), RW) Judgment of the correctness of the learner response 
	 		$this->response->data->cmi->interactions->n->latency                        = "";  // (timeinterval (second,10,2), RW) Time elapsed between the time the interaction was made available to the learner for response and the time of the first response 
	 		$this->response->data->cmi->interactions->n->description                    = "";  // (localized_string_type (SPM: 250), RW) Brief informative description of the interaction 
	 		*/
	 		$this->response->data->cmi->launch_data                                     = "";  // (characterstring (SPM: 4000), RO) Data provided to a SCO after launch, initialized from the dataFromLMS manifest element 
	 		
	 		$this->response->data->cmi->learner_id                                      = "$this->student_id"; // (long_identifier_type (SPM: 4000), RO) Identifies the learner on behalf of whom the SCO was launched 
	 		$this->response->data->cmi->learner_name                                    = "Simulated User";  // (localized_string_type (SPM: 250), RO) Name provided for the learner by the LMS 
	 		
	 		$this->response->data->cmi->learner_preference->_children                   = "audio_level,language,delivery_speed,audio_captioning";  
	 		                                                                             // (audio_level,language,delivery_speed,audio_captioning, RO) Listing of supported data model elements 
	 		$this->response->data->cmi->learner_preference->audio_level                 = "";  // (real(10,7), range (0..*), RW) Specifies an intended change in perceived audio level 
	 		$this->response->data->cmi->learner_preference->language                    = "";  // (language_type (SPM 250), RW) The learnerÕs preferred language for SCOs with multilingual capability 
	 		$this->response->data->cmi->learner_preference->delivery_speed              = "";  // (real(10,7), range (0..*), RW) The learnerÕs preferred relative speed of content delivery 
	 		$this->response->data->cmi->learner_preference->audio_captioning            = "";  // (state (-1,0,1), RW) Specifies whether captioning text corresponding to audio is displayed 
	 		
	 		$this->response->data->cmi->location                                        = "";  // (characterstring (SPM: 1000), RW) The learner's current location in the SCO 
	 		$this->response->data->cmi->max_time_allowed                                = "";  // (timeinterval (second,10,2), RO) Amount of accumulated time the learner is allowed to use a SCO 
	 		$this->response->data->cmi->mode                                            = "$this->student_mode";  // (state (browse, normal, review), RO) Identifies one of three possible modes in which the SCO may be presented to the learner 
	 		
	 		$this->response->data->cmi->objectives->_children                           = "id,score,success_status,completion_status,description";  
	 		                                                                             // (id,score,success_status,completion_status,description, RO) Listing of supported data model elements 
	 		$this->response->data->cmi->objectives->_count                              = "0"; // (non-negative integer, RO) Current number of objectives being stored by the LMS cmi.objectives.n.id (long_identifier_type (SPM: 4000), RW) Unique label for the objective 
	 		/*
	 		$this->response->data->cmi->objectives->n->score->_children                 = "";  // (scaled,raw,min,max, RO) Listing of supported data model elements 
	 		$this->response->data->cmi->objectives->n->score->scaled                    = "";  // (real (10,7) range (-1..1), RW) Number that reflects the performance of the learner for the objective 
	 		$this->response->data->cmi->objectives->n->score->raw                       = "";  // (real (10,7), RW) Number that reflects the performance of the learner, for the objective, relative to the range bounded by the values of min and max 
	 		$this->response->data->cmi->objectives->n->score->min                       = "";  // (real (10,7), RW) Minimum value, for the objective, in the range for the raw score 
	 		$this->response->data->cmi->objectives->n->score->max                       = "";  // (real (10,7), RW) Maximum value, for the objective, in the range for the raw score 
	 		$this->response->data->cmi->objectives->n->success_status                   = "";  // (state (passed, failed, unknown), RW) Indicates whether the learner has mastered the objective 
	 		$this->response->data->cmi->objectives->n->completion_status                = "";  // (state (completed, incomplete, not attempted, unknown), RW) Indicates whether the learner has completed the associated objective 
	 		$this->response->data->cmi->objectives->n->progress_measure                 = "";  // (real (10,7) range (0..1), RW) Measure of the progress the learner has made toward completing the objective 
	 		$this->response->data->cmi->objectives->n->description                      = "";  // (localized_string_type (SPM: 250), RW) Provides a brief informative description of the objective 
	 		*/
	 		$this->response->data->cmi->progress_measure                                = "";  // (real (10,7) range (0..1), RW) Measure of the progress the learner has made toward completing the SCO 
	 		$this->response->data->cmi->scaled_passing_score                            = "";  // (real(10,7) range (-1 .. 1), RO) Scaled passing score required to master the SCO 
	 		
	 		$this->response->data->cmi->score->_children                                = "scaled,raw,min,max";  
	 		                                                                             // (scaled,raw,min,max, RO) Listing of supported data model elements 
	 		$this->response->data->cmi->score->scaled                                   = "";  // (real (10,7) range (-1..1), RW) Number that reflects the performance of the learner 
	 		$this->response->data->cmi->score->raw                                      = "";  // (real (10,7), RW) Number that reflects the performance of the learner relative to the range bounded by the values of min and max 
	 		$this->response->data->cmi->score->min                                      = "";  // (real (10,7), RW) Minimum value in the range for the raw score 
	 		$this->response->data->cmi->score->max                                      = "";  // (real (10,7), RW) Maximum value in the range for the raw score 
	 		
	 		$this->response->data->cmi->session_time                                    = "PT0H0M0S";  // (timeinterval (second,10,2), WO) Amount of time that the learner has spent in the current learner session for this SCO 
	 		$this->response->data->cmi->success_status                                  = "";  // (state (passed, failed, unknown), RW) Indicates whether the learner has mastered the SCO 
	 		$this->response->data->cmi->suspend_data                                    = "";  // (characterstring (SPM: 64000), RW) Provides space to store and retrieve data between learner sessions 
	 		$this->response->data->cmi->time_limit_action                               = "";  // (state (exit,message, continue,message, exit,no message, continue,no message), RO) Indicates what the SCO should do when cmi.max_time_allowed is exceeded 
	 		$this->response->data->cmi->total_time                                      = "PT0H0M0S";  // (timeinterval (second,10,2), RO) Sum of all of the learnerÕs session times accumulated in the current learner attempt 
	 	    
	 	    $this->response->data->adl->nav->request                                   = "";  // (request(continue, previous, choice, exit, exitAll, abandon, abandonAll, _none_), RW) Navigation request to be processed immediately following Terminate() 
	 		$this->response->data->adl->nav->request_valid->continue                   = "";  // (state (true, false, unknown), RO) Used by a SCO to determine if a Continue navigation request will succeed
	 		$this->response->data->adl->nav->request_valid->previous                   = "";  // (state (true, false, unknown), RO) Used by a SCO to determine if a Previous navigation request will succeed 
	 		$this->response->data->adl->nav->request_valid->choice                     = "";  // (state (true, false, unknown), RO) Used by a SCO to determine if a Choice navigation request for a particular activity will succeed
	 		
/**
* Shared State Persistence Bolt on
* Slightly undocumented, least not in any online diagrams.  Had to rip it out of highly abiguous ADL documetation
* Since JSON was established around 2001-2, this format isn't really a object with name values just some ISO-10646-1 standard.  Sigh...
*/
	 		$this->response->data->ssp->allocate                                        = ""; // Value Space  ISO-10646-1
	 		/* Format of Allocate
	 		
	 		    {
	 				bucketID=" + id + "       // bucketID <GUID>
	 			}
	 			{
	 				requested=" + max + "     // requested {non-negative integer} (required)
	 			}
	 			{
	 				minimum=" + min + "       // minimum  {non-negative integer} (optional)
	 			}
	 			{
	 				reducible=false           // reducible {boolean} (optional) defaults to false, has no effect unless the minimum is defined
	 			}
	 			{
	 				persistence=" + per + "   // persistence (optional) defaults to 'learner'.  Can also be 'course' or 'session'
	 			}
	 			{
	 				type=""                   // type <GUID> optional, if not included LMS shall not assume any value for the buckets type
	 			}

	 		*/
	 		
	 		$this->response->data->ssp->_count                                          = "0"; // (non-negative integer, RO) Number of managed buckets
	 		/*
	 		$this->response->data->ssp->n->id                                            = "";  // {string} identifier for Nth managed bucket
	 		$this->response->data->ssp->n->allocation_success                            = "";  // {string} failure, minimum, requested tokens
	 		$this->response->data->ssp->n->data                                          = "";  // {string} SCO's data storage
	 		$this->response->data->ssp->n->bucket_state                                  = "";  // {string} totalSpace=nonNegInt  used=non-negInt  type=<GUID>
	 		$this->response->data->ssp->n->appendData // Ugh ... I guess if your building a string but I'm not planning on supporting this.
	 		*/
	 		
	 		$this->response->format = "new";
	 	} 
		
		$this->response->status = "success";
        return $this->response;
    }
    
    private function sendError($msg) {
    	$this->response->status = "error";
    	$this->response->msg = $msg;
    	return $this->response;
    }	
}
?>