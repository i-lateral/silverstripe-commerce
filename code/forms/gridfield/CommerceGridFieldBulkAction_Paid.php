<?php

/**
 * A {@link GridFieldBulkActionHandler} for bulk marking orders as dispatched
 *
 * @package commerce
 */
class CommerceGridFieldBulkAction_Paid extends GridFieldBulkActionHandler
{

    private static $allowed_actions = array(
        'paid'
    );

    private static $url_handlers = array(
        'paid' => 'paid'
    );

    public function paid(SS_HTTPRequest $request)
    {
        $ids = array();

        foreach ($this->getRecords() as $record) {
            array_push($ids, $record->ID);

            $record->Status = 'paid';
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
