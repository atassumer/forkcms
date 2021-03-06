<?php

namespace Frontend\Modules\Search\Ajax;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Frontend\Core\Engine\Base\AjaxAction as FrontendBaseAJAXAction;
use Frontend\Core\Engine\Model as FrontendModel;
use Frontend\Modules\Search\Engine\Model as FrontendSearchModel;

/**
 * This is the save-action, it will save the searched term in the statistics
 *
 * @author Matthias Mullie <forkcms@mullie.eu>
 */
class Save extends FrontendBaseAJAXAction
{
    /**
     * @var array
     */
    private $statistics;

    /**
     * Execute the action
     */
    public function execute()
    {
        parent::execute();

        // get parameters
        $searchTerm = \SpoonFilter::getPostValue('term', null, '');
        $term = (SPOON_CHARSET == 'utf-8') ? \SpoonFilter::htmlspecialchars($searchTerm) : \SpoonFilter::htmlentities(
            $searchTerm
        );

        // validate search term
        if ($term == '') {
            $this->output(self::BAD_REQUEST, null, 'term-parameter is missing.');
        } else {
            // previous search result
            $previousTerm = \SpoonSession::exists('searchTerm') ? \SpoonSession::get('searchTerm') : '';
            \SpoonSession::set('searchTerm', '');

            // save this term?
            if ($previousTerm != $term) {
                // format data
                $this->statistics = array();
                $this->statistics['term'] = $term;
                $this->statistics['language'] = FRONTEND_LANGUAGE;
                $this->statistics['time'] = FrontendModel::getUTCDate();
                $this->statistics['data'] = serialize(array('server' => $_SERVER));
                $this->statistics['num_results'] = FrontendSearchModel::getTotal($term);

                // save data
                FrontendSearchModel::save($this->statistics);
            }

            // save current search term in cookie
            \SpoonSession::set('searchTerm', $term);

            // output
            $this->output(self::OK);
        }
    }
}
