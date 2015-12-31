<?php

/**
 * A {@link GridFieldBulkActionHandler} for bulk marking orders as dispatched
 *
 * @package commerce
 */
class CommerceGridFieldBulkAction_Dispatched extends GridFieldBulkActionHandler
{

    private static $allowed_actions = array(
        'dispatched'
    );

    private static $url_handlers = array(
        'dispatched' => 'dispatched'
    );

    public function dispatched(SS_HTTPRequest $request)
    {
        $ids = array();

        foreach ($this->getRecords() as $record) {
            array_push($ids, $record->ID);

            $record->Status = 'dispatched';
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
