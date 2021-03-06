<?php

/**
 * Moodle Magistere_offer local plugin
 * This class course_form is a form used on the course offer page
 *
 * @package    local_magistere_offers
 * @copyright  2020 TCS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir."/formslib.php");

class course_form extends moodleform
{
    private $labels;

    /**
     * The constructor function calls the abstract function definition() and it will then
     * process and clean and attempt to validate incoming data.
     *
     * It will call your custom validate method to validate data and will also check any rules
     * you have specified in definition using addRule
     *
     * The name of the form (id attribute of the form) is automatically generated depending on
     * the name you gave the class extending moodleform. You should call your class something
     * like
     *
     * @param mixed $action the action attribute for the form. If empty defaults to auto detect the
     *              current url. If a moodle_url object then outputs params as hidden variables.
     * @param mixed $customdata if your form defintion method needs access to data such as $course
     *              $cm, etc. to construct the form definition then pass it in this array. You can
     *              use globals for somethings.
     * @param string $method if you set this to anything other than 'post' then _GET and _POST will
     *               be merged and used as incoming data to the form.
     * @param string $target target frame for form submission. You will rarely use this. Don't use
     *               it if you don't need to as the target attribute is deprecated in xhtml strict.
     * @param mixed $attributes you can pass a string of html attributes here or an array.
     * @param bool $editable
     * @param array $ajaxformdata Forms submitted via ajax, must pass their data here, instead of relying on _GET and _POST.
     */
    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true, array $ajaxformdata = null)
    {


        $this->labels = array(
            'domaine' => get_string('domains', 'local_magistere_offers'),
            'public' => get_string('public', 'local_magistere_offers'),
            'autoformation' => get_string('autoformation', 'local_magistere_offers'),
            'nature' => get_string('nature', 'local_magistere_offers'),
            'accompanied_course' => get_string('accompanied_course', 'local_magistere_offers'),
            'professional_community' => get_string('professional_community', 'local_magistere_offers'),
            'origin' => get_string('origin', 'local_magistere_offers'),
//            'origin_national' => get_string('origin_national', 'local_magistere_offers'),
//            'origin_shared' => get_string('origin_shared', 'local_magistere_offers'),
            'duration' => get_string('duration', 'local_magistere_offers'),
            'duration_1' => get_string('duration_1', 'local_magistere_offers'),
            'duration_2' => get_string('duration_2', 'local_magistere_offers'),
            'duration_3' => get_string('duration_3', 'local_magistere_offers'),
            'duration_4' => get_string('duration_4', 'local_magistere_offers'),
            'buttons' => get_string('buttons', 'local_magistere_offers')
        );

        parent::__construct($action, $customdata, $method, $target, $attributes, $editable, $ajaxformdata);
    }

    /***
     * 
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    public function definition() {
        global $DB;

        $mform =& $this->_form;
        $mform->_attributes['class'] = 'mform filter-course';

        $display = $this->_customdata['v'];

        $mform->addElement('hidden', 'v');
        $mform->setDefault('v', $display);
        $mform->setType('v', PARAM_NOTAGS);

        $mform->addElement('hidden', 'search_name');
        $mform->setDefault('search_name', "");
        $mform->setType('search_name', PARAM_NOTAGS);

        if (OfferCourse::isIndexationAvailable()){
            
            $mform->setType('publics', PARAM_RAW);
            $mform->setDefault('publics', $this->_customdata['publics']);
            
            $domains = OfferCourse::get_indexation_domains();
            $checkbox = array();
            $attributes = array('class' => 'domain-checkbox');
            foreach($domains as $id => $data) {
                $checkbox[] =& $mform->createElement('checkbox', 'domains['.$id.']', '', $data->name, $attributes);
            }
            $mform->addElement('header', 'domainsection', $this->labels['domaine']);
    
            $mform->addGroup($checkbox, 'domain_group', null, array(''), false);
            // $mform->setDefault('domains['.$domain.']', true);
    
            $publics = OfferCourse::get_indexation_publics();
            $checkbox = array();
            $attributes = array('class' => 'public-checkbox');
            foreach($publics as $id => $data) {
                $checkbox[] =& $mform->createElement('checkbox', 'publics['.$id.']', '', $data->name, $attributes);
            }
            $mform->addElement('header', 'publicsection', $this->labels['public']);
            $mform->addGroup($checkbox, 'public_group', null, array(''), false);
            $mform->setDefault('public_group', 0);
            $mform->setExpanded('publicsection');
    
            $mform->addElement('header', 'naturesection', $this->labels['nature']);
            $checkbox = array();
            $checkbox[] = $mform->createElement('checkbox', 'natures[autoformation]', '', $this->labels['autoformation'], 0);
            $checkbox[] = $mform->createElement('checkbox', 'natures[professional_community]', '', $this->labels['professional_community'], 0);
            $checkbox[] = $mform->createElement('checkbox', 'natures[accompanied_course]', '', $this->labels['accompanied_course'], 0);
            $mform->addGroup($checkbox, 'nature_group', null, array(''), false);
            $mform->setExpanded('naturesection');
    
            $origins = OfferCourse::get_course_indexation_origins();
            $checkbox = array();
            foreach($origins as $id => $data) {
                $checkbox[] =& $mform->createElement('checkbox', 'origins['.$id.']', '', $data, 0);
            }
            $mform->addElement('header', 'originsection', $this->labels['origin']);
            $mform->addGroup($checkbox, 'origin_group', null, array(''), false);
            $mform->setDefault('origin_group', 0);
            $mform->setExpanded('originsection');
    
            $mform->addElement('header', 'durationsection', $this->labels['duration']);
            $checkbox = array();
            $checkbox[] = $mform->createElement('checkbox', 'durations[1]', '', $this->labels['duration_1'], 0);
            $checkbox[] = $mform->createElement('checkbox', 'durations[2]', '', $this->labels['duration_2'], 0);
            $checkbox[] = $mform->createElement('checkbox', 'durations[3]', '', $this->labels['duration_3'], 0);
            $checkbox[] = $mform->createElement('checkbox', 'durations[4]', '', $this->labels['duration_4'], 0);
            $mform->addGroup($checkbox, 'nature_group', null, array(''), false);
            $mform->setExpanded('durationsection');
        }

        $mform->disable_form_change_checker();
    }

    /***
     * 
     * {@inheritDoc}
     * @see moodleform::get_data()
     */
    public function get_data()
    {
        $data = parent::get_data();

        if(!$data){
            return $data;
        }

        return $data;
    }

    public function definition_after_data() {
        parent::definition_after_data();

        $mform =& $this->_form;
        $domain_checkboxes =& $mform->getElement('domain_group')->getElements();
        foreach ($domain_checkboxes as $domain_checkbox) {
            if (isset($domain_checkbox->_attributes['checked'])) {
                $domain_checkbox->_attributes['class'] = "domain-checkbox checked";
            }
        }
    }

}