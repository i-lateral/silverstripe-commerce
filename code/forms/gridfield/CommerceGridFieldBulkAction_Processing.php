<?php

/**
 * A {@link GridFieldBulkActionHandler} for bulk marking orders as dispatched
 *
 * @package commerce
 */
class CommerceGridFieldBulkAction_Processing extends GridFieldBulkActionHandler
{

    private static $allowed_actions = array(
        'processing'
    );

    private static $url_handlers = array(
        'processing' => 'processing'
    );

    public function processing(SS_HTTPRequest $request)
    {
        $ids = array();

        foreach ($this->getRecords() as $record) {
            array_push($ids, $record->ID);

            $record->Status = 'processing';
            $record->write();
        }

        $response = new SS_HTTPResponse(Convert::raw2json(array(
            'done' => true,
            'records' => $ids
        )));

        $response->addHeader('Content-Type', 'text/json');

        return $response;
    }
}
