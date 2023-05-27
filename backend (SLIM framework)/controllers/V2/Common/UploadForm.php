<?php

namespace controllers\V2\Common;

use Carbon\Carbon;
use helpers\Cryptor;
use helpers\Responder;
use models\V2\ApplicantFormModel as ApplicantForm;
use models\V2\ApplicantModel as Applicant;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

class UploadForm
{
    protected $app;

    /**
     * @var Cryptor
     */
    private $crypto;

    function __construct($app)
    {
        $this->app = $app;
        $this->crypto = new Cryptor();
    }

    public function upload(Request $request, Response $response)
    {
        $validation = $this->app->validator->validate($request, [
            'id' => v::digit()->notEmpty(),
            'detail_id' => v::digit()->notEmpty(),
            'prod_id' => v::alnum()->notEmpty(),
            'bank_id' => v::digit()->notEmpty(),
        ]);

        if ($validation->failed()) {
            return Responder::error($response, null, 422, $validation->getError());
        }


        $req = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();
        $applicantID = $req["id"];
        $detailID = $req["detail_id"];
        $productID = $req["prod_id"];
        $bankID = $req["bank_id"];

        if (empty($uploadedFiles['file'])) {
            return Responder::error($response, null, 422, "Please Upload Your Document!");
        }

        $uploadedFile = $uploadedFiles['file'];

        $fileValidationResult = v::file()->oneOf(
            v::mimetype('image/png'),
            v::mimetype('image/jpeg'),
            v::mimetype('application/pdf'))
            ->size(null, '3MB')
            ->validate($uploadedFile->file);

        if (!$fileValidationResult) {
            return Responder::error($response, "File must be valid pdf or image file and maximum 3MB size", 422);
        }

        $status = false;
        foreach ($uploadedFiles as $file) {
            $filename = $file->getClientFilename();
            $mime = $file->getClientMediaType();
            $filePath = $file->file;

            $status = $this->insertBlob($applicantID, $detailID, $productID, $bankID, $mime, $filePath, $filename);

        }

        if ($status) {
            return Responder::success($response, 'Document Uploaded');
        } else {
            return Responder::error($response, "Upload Error!");
        }

    }

    public function view(Request $request, Response $response)
    {
        $hash = $request->getParam('hash');

        if (!isset($hash)) {
            return Responder::error($response, 'Not Found', 404);
        }

        $docData = ApplicantForm::select('blob_data', 'mime_type')->where('hash', $hash)->first();

        if (!$docData) {
            return Responder::error($response, 'Not Found', 404);
        }

        $response->write($docData->blob_data);
        return $response->withHeader('Content-Type', $docData->mime_type);

    }

    public function delete(Request $request, Response $response)
    {
        $validation = $this->app->validator->validate($request, [
            'id' => v::digit()->notEmpty(),
            'detail_id' => v::digit()->notEmpty(),
            'hash' => v::noWhitespace()->notEmpty(),
        ]);

        if ($validation->failed()) {
            return Responder::error($response, null, 422, $validation->getError());
        }

        $req = $request->getParsedBody();
        $applicantID = $req["id"];
        $detailID = $req["detail_id"];
        $hash = $req["hash"];

        $formData = ApplicantForm::where('applicant_id', $applicantID)
            ->where('detail_id', $detailID)
            ->where('hash', $hash);

        if (!$formData->exists()) {
            return Responder::error($response, 'Not Found', 404);
        } else {
            try {
                $formData->delete();
                return Responder::success($response, 'Document Deleted');

            } catch (\Exception $e) {
                return Responder::error($response, 'Internal Server Error');
            }
        }
    }

    /**
     * @param int $id Applicant Id
     * @param int $detail_id Detail Id
     * @param string $prod_id Product Id
     * @param int $bank_id Bank Id
     * @param string $mime Mime Type
     * @param string $filePath Uploaded File Path
     * @param string $fileName File Name
     * @return bool
     */
    function insertBlob($id, $detail_id, $prod_id, $bank_id, $mime, $filePath, $fileName)
    {
        $applicant = Applicant::where('id', $id)->exists();
        if (!$applicant) {
            return false;
        }

        $randomStr = md5(rand());
        $randomStr2 = md5(rand());

        $hashData = [
            "$randomStr" => $fileName,
            "$randomStr2" => Carbon::now()->getTimestamp()
        ];


        $hash = $this->crypto->encrypt(json_encode($hashData));

        $blob = file_get_contents($filePath);

        $appDocument = ApplicantForm::updateOrCreate(['applicant_id' => $id, 'detail_id' => $detail_id], [
            'applicant_id' => $id,
            'detail_id' => $detail_id,
            'prod_id' => $prod_id,
            'bank_id' => $bank_id,
            'hash' => $hash,
            'mime_type' => $mime,
            'blob_data' => $blob,
        ]);

        if (!$appDocument) {
            return false;
        }

        return true;
    }
}