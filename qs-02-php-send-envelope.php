<?php

# PHP Quick start example: Send signing request by email.
# Copyright (c) 2018 by DocuSign, Inc.
# License: The MIT License -- https://opensource.org/licenses/MIT

require_once('vendor/autoload.php');
require_once('vendor/docusign/esign-client/autoload.php');

function send_document_for_signing(){
    #
    # The document $fileNamePath will be sent to be signed by <signer_name>

    # Settings
    # Fill in these constants
    #
    # Obtain an OAuth access token from https://developers.hqtest.tst/oauth-token-generator
    $accessToken = '';
    # Obtain your accountId from demo.docusign.com -- the account id is shown in the drop down on the
    # upper right corner of the screen by your picture or the default picture. 
    $accountId = '';
    # Recipient Information:
    $signerName = 'Larry Smith';
    $signerEmail = 'larry@example.com';
    # The document you wish to send. Path is relative to the root directory of this repo.
    $fileNamePath = 'demo_documents/World_Wide_Corp_lorem.pdf';

    # The API base_path
    $basePath = 'https://demo.docusign.net/restapi';

    # Constants
    $appPath = getcwd();

    #
    # Step 1. The envelope definition is created.
    #         One signHere tab is added.
    #         The document path supplied is relative to the working directory
    #
    # Create the component objects for the envelope definition...
    $contentBytes = file_get_contents($appPath . "/" . $fileNamePath);
    $base64FileContent =  base64_encode ($contentBytes);

    $document = new DocuSign\eSign\Model\Document([ # create the DocuSign document object 
        'document_base64' => $base64FileContent, 
        'name' => 'Example document', # can be different from actual file name
        'file_extension' => 'pdf', # many different document types are accepted
        'document_id' => '1' # a label used to reference the doc
    ]);

    $signHere = new DocuSign\eSign\Model\SignHere([ # DocuSign SignHere field/tab
        'document_id' => '1', 'page_number' => '1', 'recipient_id' => '1', 
        'tab_label' => 'SignHereTab', 'x_position' => '195', 'y_position' => '147'
    ]);

    $signer = new DocuSign\eSign\Model\Signer([ # The signer
        'email' => $signerEmail, 'name' => $signerName, 'recipient_id' => "1", 'routing_order' => "1",
        'tabs' => new DocuSign\eSign\Model\Tabs(['sign_here_tabs' => [$signHere]]) # The Tabs object wants arrays of the different field/tab types
    ]);

    # Next, create the top level envelope definition and populate it.
    $envelopeDefinition = new DocuSign\eSign\Model\EnvelopeDefinition([
        'email_subject' => "Please sign this document",
        'documents' => [$document], # The order in the docs array determines the order in the envelope
        'recipients' => new DocuSign\eSign\Model\Recipients(['signers' => [$signer]]), # The Recipients object wants arrays for each recipient type
        'status' => "sent" # requests that the envelope be created and sent.
    ]);
    
    #
    #  Step 2. Create/send the envelope.
    #
    $config = new DocuSign\eSign\Configuration();
    $config->setHost($basePath);
    $config->addDefaultHeader("Authorization", "Bearer " . $accessToken);
    $apiClient = new DocuSign\eSign\ApiClient($config);
    $envelopeApi = new DocuSign\eSign\Api\EnvelopesApi($apiClient);
    $results = $envelopeApi->createEnvelope($accountId, $envelopeDefinition);
    return $results;
};

# Mainline
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $results = send_document_for_signing();
        ?>
<html lang="en">
    <body>
    <h4>Results</h4>
    <p>Status: <?= $results['status'] ?>, Envelope ID: <?= $results['envelope_id'] ?></p>
    </body>
</html>
        <?php
    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
        if ($e instanceof DocuSign\eSign\ApiException) {
            print ("\nDocuSign API error information: \n");
            var_dump ($e->getResponseBody());
        }
    }    
    die();
}
# Since it isn't a POST, print the form:
?>
<html lang="en">
    <body>
        <form method="post">
            <input type="submit" value="Send document signature request!"
                style="width:21em;height:2em;background:#1f32bb;color:white;font:bold 1.5em arial;margin: 3em;"/>
        </form>
    </body>
</html>

