<?php
App::uses('AppModel', 'Model');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

/**
 * Attribute Model
 *
 * @property Event $Event
 */
class Attribute extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'value';

	public $virtualFields = array(
			'value' => 'IF (Attribute.value2="", Attribute.value1, CONCAT(Attribute.value1, "|", Attribute.value2))',
			'category_order' => 'IF (Attribute.category="Internal reference", "a",
IF (Attribute.category="Antivirus detection", "b",
IF (Attribute.category="Payload delivery", "c",
IF (Attribute.category="Payload installation", "d",
IF (Attribute.category="Artifacts dropped", "e",
IF (Attribute.category="Persistence mechanism", "f",
IF (Attribute.category="Network activity", "g",
IF (Attribute.category="Payload type", "h",
IF (Attribute.category="Attribution", "i",
IF (Attribute.category="External analysis", "j", "k"))))))))))'); 	// TODO hardcoded

/**
 * Description field
 * explanations of certain fields to be used in various views
 *
 * @var array
 */
	public $fieldDescriptions = array(
			'signature' => array('desc' => 'Is this attribute eligible to automatically create an IDS signature (network IDS or host IDS) out of it ?'),
			'private' => array('desc' => 'Prevents upload of this single Attribute to other CyDefSIG servers', 'formdesc' => 'Prevents upload of <em>this single Attribute</em> to other CyDefSIG servers.<br/>Used only when the Event is NOT set as Private')
	);

	// these are definition of possible types + their descriptions and maybe later other behaviors
	// e.g. if the attribute should be correlated with others or not

	// if these then a category my have upload to be zipped

	public $zippedDefinitions = array(
			'malware-sample'
	);

	// if these then a category my have upload

	public $uploadDefinitions = array(
			'attachment'
	);

	public $typeDefinitions = array(
			'md5' => array('desc' => 'A checksum in md5 format', 'formdesc' => "You are encouraged to use filename|md5 instead. <br/>A checksum in md5 format, only use this if you don't know the correct filename"),
			'sha1' => array('desc' => 'A checksum in sha1 format', 'formdesc' => "You are encouraged to use filename|sha1 instead. <br/>A checksum in sha1 format, only use this if you don't know the correct filename"),
			'filename' => array('desc' => 'Filename'),
			'filename|md5' => array('desc' => 'A filename and an md5 hash separated by a |', 'formdesc' => "A filename and an md5 hash separated by a | (no spaces)"),
			'filename|sha1' => array('desc' => 'A filename and an sha1 hash separated by a |', 'formdesc' => "A filename and an sha1 hash separated by a | (no spaces)"),
			'ip-src' => array('desc' => "A source IP address of the attacker"),
			'ip-dst' => array('desc' => 'A destination IP address of the attacker or C&C server', 'formdesc' => "A destination IP address of the attacker or C&C server. <br/>Also set the IDS flag on when this IP is hardcoded in malware"),
			'hostname' => array('desc' => 'A full host/dnsname of an attacker', 'formdesc' => "A full host/dnsname of an attacker. <br/>Also set the IDS flag on when this hostname is hardcoded in malware"),
			'domain' => array('desc' => 'A domain name used in the malware', 'formdesc' => "A domain name used in the malware. <br/>Use this instead of hostname when the upper domain is <br/>important or can be used to create links between events."),
			'email-src' => array('desc' => "The email address (or domainname) used to send the malware."),
			'email-dst' => array('desc' => "A recipient email address", 'formdesc' => "A recipient email address that is not related to your constituency."),
			'email-subject' => array('desc' => "The subject of the email"),
			'email-attachment' => array('desc' => "File name of the email attachment."),
			'url' => array('desc' => 'url'),
			'user-agent' => array('desc' => "The user-agent used by the malware in the HTTP request."),
			'regkey' => array('desc' => "Registry key or value"),
			'regkey|value' => array('desc' => "Registry value + data separated by |"),
			'AS' => array('desc' => 'Autonomous system'),
			'snort' => array('desc' => 'An IDS rule in Snort rule-format', 'formdesc' => "An IDS rule in Snort rule-format. <br/>This rule will be automatically rewritten in the NIDS exports."),
			'pattern-in-file' => array('desc' => 'Pattern in file that identifies the malware'),
			'pattern-in-traffic' => array('desc' => 'Pattern in network traffic that identifies the malware'),
			'pattern-in-memory' => array('desc' => 'Pattern in memory dump that identifies the malware'),
			'yara' => array('desc' => 'Yara signature'),
			'vulnerability' => array('desc' => 'A reference to the vulnerability used in the exploit'),
			'attachment' => array('desc' => 'Attachment with external information', 'formdesc' => "Please upload files using the <em>Upload Attachment</em> button."),
			'malware-sample' => array('desc' => 'Attachment containing encrypted malware sample', 'formdesc' => "Please upload files using the <em>Upload Attachment</em> button."),
			'link' => array('desc' => 'Link to an external information'),
			'comment' => array('desc' => 'Comment or description in a human language', 'formdesc' => 'Comment or description in a human language. <br/> This will not be correlated with other attributes (NOT IMPLEMENTED YET)'),
			'text' => array('desc' => 'Name, ID or a reference'),
			'other' => array('desc' => 'Other attribute')
	);

	// definitions of categories
	public $categoryDefinitions = array(
			'Internal reference' => array(
					'desc' => 'Reference used by the publishing party (e.g. ticket number)',
					'types' => array('link', 'comment', 'text', 'other')
					),
			'Antivirus detection' => array(
					'desc' => 'All the info about how the malware is detected by the antivirus products',
					'formdesc' => 'List of anti-virus vendors detecting the malware or information on detection performance (e.g. 13/43 or 67%).<br/>Attachment with list of detection or link to VirusTotal could be placed here as well.',
					'types' => array('link', 'comment', 'text', 'attachment', 'other')
					),
			'Payload delivery' => array(
					'desc' => 'Information about how the malware is delivered',
					'formdesc' => 'Information about the way the malware payload is initially delivered, <br/>for example information about the email or web-site, vulnerability used, originating IP etc. <br/>Malware sample itself should be attached here.',
					'types' => array('md5', 'sha1', 'filename', 'filename|md5', 'filename|sha1', 'ip-src', 'ip-dst', 'hostname', 'domain', 'email-src', 'email-dst', 'email-subject', 'email-attachment', 'url', 'ip-dst', 'user-agent', 'AS', 'pattern-in-file', 'pattern-in-traffic', 'yara', 'attachment', 'malware-sample', 'link', 'comment', 'text', 'vulnerability', 'other')
					),
			'Artifacts dropped' => array(
					'desc' => 'Any artifact (files, registry keys etc.) dropped by the malware or other modifications to the system',
					'types' => array('md5', 'sha1', 'filename', 'filename|md5', 'filename|sha1', 'regkey', 'regkey|value', 'pattern-in-file', 'pattern-in-memory', 'yara', 'attachment', 'malware-sample', 'comment', 'text', 'other')
					),
			'Payload installation' => array(
					'desc' => 'Info on where the malware gets installed in the system',
					'formdesc' => 'Location where the payload was placed in the system and the way it was installed.<br/>For example, a filename|md5 type attribute can be added here like this:<br/>c:\\windows\\system32\\malicious.exe|41d8cd98f00b204e9800998ecf8427e.',
					'types' => array('md5', 'sha1', 'filename', 'filename|md5', 'filename|sha1', 'pattern-in-file', 'pattern-in-traffic', 'pattern-in-memory', 'yara', 'vulnerability', 'attachment', 'malware-sample', 'comment', 'text', 'other')
					),
			'Persistence mechanism' => array(
					'desc' => 'Mechanisms used by the malware to start at boot',
					'formdesc' => 'Mechanisms used by the malware to start at boot.<br/>This could be a registry key, legitimate driver modification, LNK file in startup',
					'types' => array('filename', 'regkey', 'regkey|value', 'comment', 'text', 'other')
					),
			'Network activity' => array(
					'desc' => 'Information about network traffic generated by the malware',
					'types' => array('ip-src', 'ip-dst', 'hostname', 'domain', 'email-dst', 'url', 'user-agent', 'AS', 'snort', 'pattern-in-file', 'pattern-in-traffic', 'attachment', 'comment', 'text', 'other')
					),
			'Payload type' => array(
					'desc' => 'Information about the final payload(s)',
					'formdesc' => 'Information about the final payload(s).<br/>Can contain a function of the payload, e.g. keylogger, RAT, or a name if identified, such as Poison Ivy.',
					'types' => array('comment', 'text', 'other')
					),
			'Attribution' => array(
					'desc' => 'Identification of the group, organisation, or coountry behind the attack',
					'types' => array('comment', 'text', 'other')
					),
			'External analysis' => array(
					'desc' => 'Any other result from additional analysis of the malware like tools output',
					'formdesc' => 'Any other result from additional analysis of the malware like tools output<br/>Examples: pdf-parser output, automated sandbox analysis, reverse engineering report.',
					'types' => array('md5', 'sha1', 'filename', 'filename|md5', 'filename|sha1', 'ip-src', 'ip-dst', 'hostname', 'domain', 'url', 'user-agent', 'regkey', 'regkey|value', 'AS', 'snort', 'pattern-in-file', 'pattern-in-traffic', 'pattern-in-memory', 'vulnerability', 'attachment', 'malware-sample', 'link', 'comment', 'text', 'other')
					),
			'Other' => array(
					'desc' => 'Attributes that are not part of any other category',
					'types' => array('comment', 'text', 'other')
					)
	);

	public  $order = array("Attribute.event_id" => "DESC", "Attribute.type" => "ASC");

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'event_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'type' => array(
			// currently when adding a new attribute type we need to change it in both places
			'rule' => array('validateTypeValue'),
			'message' => 'Options depend on the selected category.',
			//'allowEmpty' => false,
			'required' => true,
			//'last' => false, // Stop validation after this rule
			//'on' => 'create', // Limit validation to 'create' or 'update' operations

		),
		// this could be initialized from categoryDefinitions but dunno how at the moment
		'category' => array(
			'rule' => array('inList', array(
							'Internal reference',
							'Antivirus detection',
							'Payload delivery',
							'Payload installation',
							'Artifacts dropped',
							'Persistence mechanism',
							'Network activity',
							'Payload type',
							'Attribution',
							'External analysis',
							'Other',
							'' // FIXME remove this once all attributes have a category. Otherwise sigs without category are not shown in the list
						)),
			'message' => 'Options : Payload delivery, Antivirus detection, Payload installation, Files dropped ...'
		),
		'value' => array(
			'notempty' => array(
			'rule' => array('notempty'),
			'message' => 'Please fill in this field',
			//'allowEmpty' => false,
			//'required' => false,
			//'last' => false, // Stop validation after this rule
			//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
			'userdefined' => array(
				'rule' => array('validateAttributeValue'),
				'message' => 'Value not in the right type/format. Please double check the value or select "other" for a type.',
				//'allowEmpty' => false,
				//'required' => true,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
			'unique' => array(
					'rule' => array('valueIsUnique'),
					'message' => 'A similar attribute already exists for this event.',
					//'allowEmpty' => false,
					//'required' => true,
					//'last' => false, // Stop validation after this rule
					//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'to_ids' => array(
			'boolean' => array(
				'rule' => array('boolean'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'uuid' => array(
			'uuid' => array(
				'rule' => array('uuid'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'revision' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'private' => array(
				'boolean' => array(
						'rule' => array('boolean'),
						//'message' => 'Your custom message here',
						'allowEmpty' => true,
						'required' => false,
						//'last' => false, // Stop validation after this rule
						//'on' => 'create', // Limit validation to 'create' or 'update' operations
				),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Event' => array(
			'className' => 'Event',
			'foreignKey' => 'event_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * beforeSave
 *
 * @throws InternalErrorException
 * @return bool always true
 */
	public function beforeSave() {
		// increment the revision number
		if (empty($this->data['Attribute']['revision'])) {
			$this->data['Attribute']['revision'] = 0;
		}
		$this->data['Attribute']['revision'] = 1 + $this->data['Attribute']['revision'];

		// explode value of composite type in value1 and value2
		// or copy value to value1 if not composite type
		if (!empty($this->data['Attribute']['type'])) {
			$compositeTypes = $this->getCompositeTypes();
			// explode composite types in value1 and value2
			$pieces = explode('|', $this->data['Attribute']['value']);
			if (in_array($this->data['Attribute']['type'], $compositeTypes)) {
				if (2 != count($pieces)) {
					throw new InternalErrorException('Composite type, but value not explodable');
				}
				$this->data['Attribute']['value1'] = $pieces[0];
				$this->data['Attribute']['value2'] = $pieces[1];
			} else {
				$total = implode('|', $pieces);
				$this->data['Attribute']['value1'] = $total;
				$this->data['Attribute']['value2'] = '';
			}
		}

		// always return true after a beforeSave()
		return true;
	}

	public function afterSave() {
		if ('db' == Configure::read('CyDefSIG.correlation')) {
			// update correlation..
			$this->__afterSaveCorrelation($this->data['Attribute']);
		}

		$result = true;
		// if the 'data' field is set on the $this->data then save the data to the correct file
		if (isset($this->data['Attribute']['type']) && $this->typeIsAttachment($this->data['Attribute']['type']) && !empty($this->data['Attribute']['data'])) {
			$result = $result && $this->saveBase64EncodedAttachment($this->data['Attribute']);
		}
		return $result;
	}

	public function beforeDelete() {
		// delete attachments from the disk
		$this->read();  // first read the attribute from the db
		if ($this->typeIsAttachment($this->data['Attribute']['type'])) {
			// FIXME secure this filesystem access/delete by not allowing to change directories or go outside of the directory container.
			// only delete the file if it exists
			$filepath = APP . "files" . DS . $this->data['Attribute']['event_id'] . DS . $this->data['Attribute']['id'];
			$file = new File ($filepath);
			if ($file->exists()) {
				if (!$file->delete()) {
					throw new InternalErrorException('Delete of file attachment failed. Please report to administrator.');
				}
			}
		}

		if ('db' == Configure::read('CyDefSIG.correlation')) {
			// update correlation..
			$this->__beforeDeleteCorrelation($this->data['Attribute']['id']);
		}
	}

	public function beforeValidate() {
		// remove leading and trailing blanks
		$this->data['Attribute']['value'] = trim($this->data['Attribute']['value']);

		if (!isset($this->data['Attribute']['type'])) {
			return false;
		}

		switch($this->data['Attribute']['type']) {
			// lowercase these things
			case 'md5':
			case 'sha1':
			case 'domain':
			case 'hostname':
				$this->data['Attribute']['value'] = strtolower($this->data['Attribute']['value']);
				break;
			case 'filename|md5':
			case 'filename|sha1':
				$pieces = explode('|', $this->data['Attribute']['value']);
				$this->data['Attribute']['value'] = $pieces[0] . '|' . strtolower($pieces[1]);
				break;
		}

		// generate UUID if it doesn't exist
		if (empty($this->data['Attribute']['uuid'])) {
			$this->data['Attribute']['uuid'] = String::uuid();
		}

		// always return true, otherwise the object cannot be saved
		return true;
	}

	public function valueIsUnique ($fields) {
		$value = $fields['value'];
		$eventId = $this->data['Attribute']['event_id'];
		$type = $this->data['Attribute']['type'];
		$toIds = $this->data['Attribute']['to_ids'];
		$category = $this->data['Attribute']['category'];

		// check if the attribute already exists in the same event
		$conditions = array('Attribute.event_id' => $eventId,
				'Attribute.type' => $type,
				'Attribute.category' => $category,
				'Attribute.value' => $value
		);
		if (isset($this->data['Attribute']['id'])) {
			$conditions['Attribute.id !='] = $this->data['Attribute']['id'];
		}

		$params = array('recursive' => 0,
				'conditions' => $conditions,
		);
		if (0 != $this->find('count', $params)) {
			return false;
		}

		// Say everything is fine
		return true;
	}

	public function validateTypeValue($fields) {
		$category = $this->data['Attribute']['category'];
		if (isset($this->categoryDefinitions[$category]['types'])) {
			return in_array($fields['type'], $this->categoryDefinitions[$category]['types']);
		}
		return false;
	}

	public function validateAttributeValue($fields) {
		$value = $fields['value'];
		$returnValue = false;

		// check data validation
		switch($this->data['Attribute']['type']) {
			case 'md5':
				if (preg_match("#^[0-9a-f]{32}$#", $value)) {
					$returnValue = true;
				} else {
					$returnValue = 'Checksum has invalid length or format. Please double check the value or select "other" for a type.';
				}
				break;
			case 'sha1':
				if (preg_match("#^[0-9a-f]{40}$#", $value)) {
					$returnValue = true;
				} else {
					$returnValue = 'Checksum has invalid length or format. Please double check the value or select "other" for a type.';
				}
				break;
			case 'filename':
				// no newline
				if (!preg_match("#\n#", $value)) {
					$returnValue = true;
				}
				break;
			case 'filename|md5':
				// no newline
				if (preg_match("#^.+\|[0-9a-f]{32}$#", $value)) {
					$returnValue = true;
				} else {
					$returnValue = 'Checksum has invalid length or format. Please double check the value or select "other" for a type.';
				}
				break;
			case 'filename|sha1':
				// no newline
				if (preg_match("#^.+\|[0-9a-f]{40}$#", $value)) {
					$returnValue = true;
				} else {
					$returnValue = 'Checksum has invalid length or format. Please double check the value or select "other" for a type.';
				}
				break;
			case 'ip-src':
				$parts = explode("/", $value);
				// [0] = the ip
				// [1] = the network address
				if (count($parts) <= 2 ) {
					// ipv4 and ipv6 matching
					if (filter_var($parts[0],FILTER_VALIDATE_IP)) {
						// ip is validated, now check if we have a valid network mask
						if (empty($parts[1])) {
							$returnValue = true;
						} else {
							if (is_numeric($parts[1]) && $parts[1] < 129) {
								$returnValue = true;
							}
						}
					}
				}
				if (!$returnValue) {
					$returnValue = 'IP address has invalid format. Please double check the value or select "other" for a type.';
				}
				break;
			case 'ip-dst':
				$parts = explode("/", $value);
				// [0] = the ip
				// [1] = the network address
				if (count($parts) <= 2 ) {
					// ipv4 and ipv6 matching
					if (filter_var($parts[0],FILTER_VALIDATE_IP)) {
						// ip is validated, now check if we have a valid network mask
						if (empty($parts[1])) {
							$returnValue = true;
						} else {
							if (is_numeric($parts[1]) && $parts[1] < 129) {
								$returnValue = true;
							}
						}
					}
				}
				if (!$returnValue) {
					$returnValue = 'IP address has invalid format. Please double check the value or select "other" for a type.';
				}
				break;
			case 'hostname':
			case 'domain':
				if (preg_match("#^[A-Z0-9.-]+\.[A-Z]{2,4}$#i", $value)) {
					$returnValue = true;
				} else {
					$returnValue = 'Domain name has invalid format. Please double check the value or select "other" for a type.';
				}
				break;
			case 'email-src':
				// we don't use the native function to prevent issues with partial email addresses
				if (preg_match("#^[A-Z0-9._%+-]*@[A-Z0-9.-]+\.[A-Z]{2,4}$#i", $value)) {
					$returnValue = true;
				} else {
					$returnValue = 'Email address has invalid format. Please double check the value or select "other" for a type.';
				}
				break;
			case 'email-dst':
				// we don't use the native function to prevent issues with partial email addresses
				if (preg_match("#^[A-Z0-9._%+-]*@[A-Z0-9.-]+\.[A-Z]{2,4}$#i", $value)) {
					$returnValue = true;
				} else {
					$returnValue = 'Email address has invalid format. Please double check the value or select "other" for a type.';
				}
				break;
			case 'email-subject':
				// no newline
				if (!preg_match("#\n#", $value)) {
					$returnValue = true;
				}
				break;
			case 'email-attachment':
				// no newline
				if (!preg_match("#\n#", $value)) {
					$returnValue = true;
				}
				break;
			case 'url':
				// no newline
				if (!preg_match("#\n#", $value)) {
					$returnValue = true;
				}
				break;
			case 'user-agent':
				// no newline
				if (!preg_match("#\n#", $value)) {
					$returnValue = true;
				}
				break;
			case 'regkey':
				// no newline
				if (!preg_match("#\n#", $value)) {
					$returnValue = true;
				}
				break;
			case 'regkey|value':
				// no newline
				if (!preg_match("#.+\|.+#", $value)) {
					$returnValue = true;
				}
				break;
			case 'snort':
				// no validation yet. TODO implement data validation on snort attribute type
			case 'other':
				$returnValue = true;
				break;
		}

		// default action is to return false
		if (!$returnValue) {
			$returnValue = true;
		}
		return $returnValue;
	}

	public function getCompositeTypes() {
		// build the list of composite Attribute.type dynamically by checking if type contains a |
		// default composite types
		$compositeTypes = array('malware-sample');	// TODO hardcoded composite
		// dynamically generated list
		foreach (array_keys($this->typeDefinitions) as $type) {
			$pieces = explode('|', $type);
			if (2 == count($pieces)) {
				$compositeTypes[] = $type;
			}
		}
		return $compositeTypes;
	}

	public function isOwnedByOrg($attributeid, $org) {
		$this->id = $attributeid;
		$this->read();
		return $this->data['Event']['org'] === $org;
	}

	public function getRelatedAttributes($attribute, $fields=array()) {
		// LATER getRelatedAttributes($attribute) this might become a performance bottleneck

		// exclude these specific categories to be linked
		switch ($attribute['category']) {
			case 'Antivirus detection':
				return null;
		}
		// exclude these specific types to be linked
		switch ($attribute['type']) {
			case 'other':
			case 'comment':
				return null;
		}

		// prepare the conditions
		$conditions = array(
				'Attribute.event_id !=' => $attribute['event_id'],
				//'Attribute.type' => $attribute['type'],  // do not filter on type
				);
		if (empty($attribute['value1'])) {	// prevent issues with empty fields
			return null;
		}

		if (empty($attribute['value2'])) {
			// no value2, only search for value 1
			$conditions['OR'] = array(
					'Attribute.value1' => $attribute['value1'],
					'Attribute.value2' => $attribute['value1'],
			);
		} else {
			// value2 also set, so search for both
			$conditions['OR'] = array(
					'Attribute.value1' => array($attribute['value1'],$attribute['value2']),
					'Attribute.value2' => array($attribute['value1'],$attribute['value2']),
			);
		}

		// do the search
		if (empty($fields)) {
			$fields = array('Attribute.*');
		}
		$similarEvents = $this->find('all',array('conditions' => $conditions,
												'fields' => $fields,
												'recursive' => 0,
												'group' => array('Attribute.event_id'),
												'order' => 'Attribute.event_id DESC', )
		);
		return $similarEvents;
	}

	public function typeIsMalware($type) {
		if (in_array($type, $this->zippedDefinitions)) {
			return true;
		} else {
			return false;
		}
	}

	public function typeIsAttachment($type) {
		if ((in_array($type, $this->zippedDefinitions)) || (in_array($type, $this->uploadDefinitions))) {
			return true;
		} else {
			return false;
		}
	}

	public function base64EncodeAttachment($attribute) {
		$filepath = APP . "files" . DS . $attribute['event_id'] . DS . $attribute['id'];
		$file = new File($filepath);
		if (!$file->exists()) {
			return '';
		}
		$content = $file->read();
		return base64_encode($content);
	}

	public function saveBase64EncodedAttachment($attribute) {
		$rootDir = APP . DS . "files" . DS . $attribute['event_id'];
		$dir = new Folder($rootDir, true);						// create directory structure
		$destpath = $rootDir . DS . $attribute['id'];
		$file = new File ($destpath, true);						// create the file
		$decodedData = base64_decode($attribute['data']);		// decode
		if ($file->write($decodedData)) {						// save the data
			return true;
		} else {
			// error
			return false;
		}
	}

	private function __afterSaveCorrelation($attribute) {
		$this->__beforeDeleteCorrelation($attribute);
		// re-add
		$this->setRelatedAttributes($attribute, array('Attribute.id', 'Attribute.event_id', 'Event.date'));
	}

	private function __beforeDeleteCorrelation($attribute) {
		$this->Correlation = ClassRegistry::init('Correlation');
		$dummy = $this->Correlation->deleteAll(array('OR' => array(
						'Correlation.1_attribute_id' => $attribute,
						'Correlation.attribute_id' => $attribute))
		);
	}

/**
 * return an array containing 'double-values'
 *
 * @return array()
 */
	public function doubleAttributes() {
		$doubleAttributes = array();

		$similarValue1 = $this->find('all',array('conditions' => array(),
												'fields' => 'value1',
												'recursive' => 0,
												'group' => 'Attribute.value1 HAVING count(1)>1' ));
		$similarValue2 = $this->find('all',array('conditions' => array(),
												'fields' => 'value2',
												'recursive' => 0,
												'group' => 'Attribute.value2 HAVING count(1)>1' ));
		$similarValues = $this->find('all', array('joins' => array(array(
															'table' => 'attributes',
															'alias' => 'att2',
															'type' => 'INNER',
															'conditions' => array('Attribute.value2 = att2.value1'))),
															'fields' => array('att2.value1')));
		$doubleAttributes = array_merge($similarValue1,$similarValue2);
		$doubleAttributes = array_merge($doubleAttributes,$similarValues);

		$double = array();
		foreach ($doubleAttributes as $key => $doubleAttribute) {
			$v = isset($doubleAttribute['Attribute']) ? $doubleAttribute['Attribute'] : $doubleAttribute['att2'];
			$v = isset($v['value1']) ? $v['value1'] : $v['value2'];
			if ($v != '') {
				$double[] = $v;
			}
		}
		return $double;
	}

	public function setRelatedAttributes($attribute, $fields=array()) {
		$this->Event = ClassRegistry::init('Event');
		$relatedAttributes = $this->getRelatedAttributes($attribute, $fields);
		if ($relatedAttributes) {
			foreach ($relatedAttributes as $relatedAttribute) {
				// and store into table
				$params = array(
					'conditions' => array('Event.id' => $relatedAttribute['Attribute']['event_id']),
					'recursive' => 0,
					'fields' => array('Event.date')
				);
				$eventDate = $this->Event->find('first', $params);
				$this->Correlation = ClassRegistry::init('Correlation');
				$this->Correlation->create();
				$this->Correlation->save(array(
					'Correlation' => array(
						'1_event_id' => $attribute['event_id'], '1_attribute_id' => $attribute['id'],
						'event_id' => $relatedAttribute['Attribute']['event_id'], 'attribute_id' => $relatedAttribute['Attribute']['id'],
						'date' => $eventDate['Event']['date']))
				);
			}
		}
	}

	public function uploadAttributeToServer($attribute, $server, $HttpSocket=null) {
		$newLocation = $this->RESTfullAttributeToServer($attribute, $server, null, $HttpSocket);
		if (is_string($newLocation)) { // HTTP/1.1 302 Found and Location: http://<newLocation>
			$newTextBody = $this->RESTfullAttributeToServer($attribute, $server, $newLocation, $HttpSocket);
		}
		return true;
	}

/**
 * Uploads the attribute to another Server
 * TODO move this to a component
 *
 * @return bool true if success, error message if failed
 */
	public function RESTfullAttributeToServer($attribute, $server, $urlPath, $HttpSocket=null) {
		// do not keep attributes that are private
		if (true == $attribute['private']) { // never upload private events
			return "Attribute is private and non exportable";
		}

		$url = $server['Server']['url'];
		$authkey = $server['Server']['authkey'];
		if (null == $HttpSocket) {
			App::uses('HttpSocket', 'Network/Http');
			$HttpSocket = new HttpSocket();
		}
		$request = array(
				'header' => array(
						'Authorization' => $authkey,
						'Accept' => 'application/xml',
						'Content-Type' => 'application/xml',
						//'Connection' => 'keep-alive' // LATER followup cakephp ticket 2854 about this problem http://cakephp.lighthouseapp.com/projects/42648-cakephp/tickets/2854
				)
		);
		$uri = isset($urlPath) ? $urlPath : $url . '/attributes';

		// LATER try to do this using a separate EventsController and renderAs() function
		$xmlArray = array();

		// cleanup the array from things we do not want to expose
		//unset($event['Event']['org']);
		// remove value1 and value2 from the output
		unset($attribute['value1']);
		unset($attribute['value2']);
		// also add the encoded attachment
		if ($this->typeIsAttachment($attribute['type'])) {
			$encodedFile = $this->base64EncodeAttachment($attribute);
			$attribute['data'] = $encodedFile;
		}

		// display the XML to the user
		$xmlArray['Attribute'] = $attribute;
		$xmlObject = Xml::fromArray($xmlArray, array('format' => 'tags'));
		$attributesXml = $xmlObject->asXML();
		// do a REST POST request with the server
		$data = $attributesXml;
		// LATER validate HTTPS SSL certificate
		$this->Dns = ClassRegistry::init('Dns');
		if ($this->Dns->testipaddress(parse_url($uri, PHP_URL_HOST))) {
			// TODO NETWORK for now do not know how to catch the following..
			// TODO NETWORK No route to host
			$response = $HttpSocket->post($uri, $data, $request);
			switch ($response->code) {
				case '200':	// 200 (OK) + entity-action-result
					if ($response->isOk()) {
						return isset($urlPath) ? $response->body() : true;
					} else {
						try {
							// parse the XML response and keep the reason why it failed
							$xmlArray = Xml::toArray(Xml::build($response->body));
						} catch (XmlException $e) {
							return true;
						}
						if (strpos($xmlArray['response']['name'], "Attribute already exists")) {	// strpos, so i can piggyback some value if needed.
							return true;
						} else {
							return $xmlArray['response']['name'];
						}
					}
					break;
				case '302': // Found
				case '404': // Not Found
					return isset($urlPath) ? $response->body() : $response->headers['Location'];
					break;
			}
		}
	}

/**
 * Deletes the attribute from another Server
 * TODO move this to a component
 *
 * @return bool true if success, error message if failed
 */
	public function deleteAttributeFromServer($uuid, $server, $HttpSocket = null) {
		$url = $server['Server']['url'];
		$authkey = $server['Server']['authkey'];
		if (null == $HttpSocket) {
			App::uses('HttpSocket', 'Network/Http');
			$HttpSocket = new HttpSocket();
		}
		$request = array(
				'header' => array(
						'Authorization' => $authkey,
						'Accept' => 'application/xml',
						'Content-Type' => 'application/xml',
						//'Connection' => 'keep-alive' // LATER followup cakephp ticket 2854 about this problem http://cakephp.lighthouseapp.com/projects/42648-cakephp/tickets/2854
				)
		);
		$uri = $url . '/attributes/0?uuid=' . $uuid;

		// LATER validate HTTPS SSL certificate
		$this->Dns = ClassRegistry::init('Dns');
		if ($this->Dns->testipaddress(parse_url($uri, PHP_URL_HOST))) {
			// TODO NETWORK for now do not know how to catch the following..
			// TODO NETWORK No route to host
			$response = $HttpSocket->delete($uri, array(), $request);
			// TODO REST, DELETE, no responce needed
		}
	}

}
