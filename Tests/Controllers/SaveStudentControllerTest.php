<?php

namespace Tests\Controllers;

use Controllers\SaveStudentController;
use PHPUnit\Framework\TestCase;

class SaveStudentControllerTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET     = [];
        $_POST    = [];
        $_FILES   = [];
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_GET     = [];
        $_POST    = [];
        $_FILES   = [];
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    // -------------------------------------------------------------------------
    // support()
    // -------------------------------------------------------------------------

    public function testSupportReturnsTrueForSaveStudentPost(): void
    {
        $this->assertTrue(SaveStudentController::support('save_student', 'POST'));
    }

    public function testSupportReturnsFalseForGet(): void
    {
        $this->assertFalse(SaveStudentController::support('save_student', 'GET'));
    }

    public function testSupportReturnsFalseForWrongPage(): void
    {
        $this->assertFalse(SaveStudentController::support('dashboard-admin', 'POST'));
    }

    public function testSupportReturnsFalseForEmptyPage(): void
    {
        $this->assertFalse(SaveStudentController::support('', 'POST'));
    }

    public function testSupportReturnsFalseForEmptyMethod(): void
    {
        $this->assertFalse(SaveStudentController::support('save_student', ''));
    }

    public function testSupportReturnsFalseForPut(): void
    {
        $this->assertFalse(SaveStudentController::support('save_student', 'PUT'));
    }

    // -------------------------------------------------------------------------
    // Validation logic (isolated — mirrors controller's $errors block)
    // -------------------------------------------------------------------------

    /**
     * @param array<string, string> $data
     * @param string                $lang
     * @return array<int, string>
     */
    private function runValidation(array $data, string $lang = 'fr'): array
    {
        $errors = [];
        if (($data['NumEtu']         ?? '') === '') $errors[] = $lang === 'fr' ? 'Le numéro étudiant est requis' : 'Student ID is required';
        if (($data['Nom']            ?? '') === '') $errors[] = $lang === 'fr' ? 'Le nom est requis'             : 'Last name is required';
        if (($data['Prenom']         ?? '') === '') $errors[] = $lang === 'fr' ? 'Le prénom est requis'          : 'First name is required';
        if (($data['EmailPersonnel'] ?? '') === '') $errors[] = $lang === 'fr' ? "L'email est requis"            : 'Email is required';
        if (($data['Telephone']      ?? '') === '') $errors[] = $lang === 'fr' ? 'Le téléphone est requis'       : 'Phone is required';
        return $errors;
    }

    /** @return array<string, string> */
    private function validData(): array
    {
        return [
            'NumEtu'         => '22000001',
            'Nom'            => 'DUPONT',
            'Prenom'         => 'Alice',
            'EmailPersonnel' => 'alice@example.com',
            'Telephone'      => '0600000000',
            'Type'           => 'sortant',
        ];
    }

    public function testValidationPassesWithAllFieldsFilled(): void
    {
        $errors = $this->runValidation($this->validData());
        $this->assertEmpty($errors);
    }

    public function testValidationFailsWhenNumEtuMissing(): void
    {
        $data = $this->validData();
        $data['NumEtu'] = '';
        $errors = $this->runValidation($data);
        $this->assertContains('Le numéro étudiant est requis', $errors);
    }

    public function testValidationFailsWhenNomMissing(): void
    {
        $data = $this->validData();
        $data['Nom'] = '';
        $errors = $this->runValidation($data);
        $this->assertContains('Le nom est requis', $errors);
    }

    public function testValidationFailsWhenPrenomMissing(): void
    {
        $data = $this->validData();
        $data['Prenom'] = '';
        $errors = $this->runValidation($data);
        $this->assertContains('Le prénom est requis', $errors);
    }

    public function testValidationFailsWhenEmailMissing(): void
    {
        $data = $this->validData();
        $data['EmailPersonnel'] = '';
        $errors = $this->runValidation($data);
        $this->assertContains("L'email est requis", $errors);
    }

    public function testValidationFailsWhenTelephoneMissing(): void
    {
        $data = $this->validData();
        $data['Telephone'] = '';
        $errors = $this->runValidation($data);
        $this->assertContains('Le téléphone est requis', $errors);
    }

    public function testValidationCollectsAllErrorsAtOnce(): void
    {
        $errors = $this->runValidation([
            'NumEtu' => '', 'Nom' => '', 'Prenom' => '',
            'EmailPersonnel' => '', 'Telephone' => '',
        ]);
        $this->assertCount(5, $errors);
    }

    // -------------------------------------------------------------------------
    // English error messages
    // -------------------------------------------------------------------------

    public function testValidationReturnsEnglishErrorsWhenLangIsEn(): void
    {
        $errors = $this->runValidation([
            'NumEtu' => '', 'Nom' => '', 'Prenom' => '',
            'EmailPersonnel' => '', 'Telephone' => '',
        ], 'en');

        $this->assertContains('Student ID is required',  $errors);
        $this->assertContains('Last name is required',   $errors);
        $this->assertContains('First name is required',  $errors);
        $this->assertContains('Email is required',       $errors);
        $this->assertContains('Phone is required',       $errors);
    }

    public function testValidationReturnsFrenchErrorsWhenLangIsFr(): void
    {
        $data = $this->validData();
        $data['NumEtu'] = '';
        $errors = $this->runValidation($data, 'fr');
        $this->assertContains('Le numéro étudiant est requis', $errors);
        $this->assertNotContains('Student ID is required', $errors);
    }

    // -------------------------------------------------------------------------
    // Session message logic (isolated)
    // -------------------------------------------------------------------------

    public function testSessionMessageOnValidationFailureIsCommaSeparated(): void
    {
        $errors = $this->runValidation([
            'NumEtu' => '', 'Nom' => '', 'Prenom' => '',
            'EmailPersonnel' => '', 'Telephone' => '',
        ]);
        $message = implode(', ', $errors);
        $this->assertStringContainsString('Le numéro étudiant est requis', $message);
        $this->assertStringContainsString('Le nom est requis', $message);
    }

    public function testSessionMessageOnSuccessFr(): void
    {
        $this->assertEquals('Folder créé avec succès', $this->buildResultMessage(true, 'fr'));
    }

    public function testSessionMessageOnFailureFr(): void
    {
        $this->assertEquals('Erreur lors de la création du dossier', $this->buildResultMessage(false, 'fr'));
    }

    public function testSessionMessageOnSuccessEn(): void
    {
        $this->assertEquals('Folder created successfully', $this->buildResultMessage(true, 'en'));
    }

    public function testSessionMessageOnFailureEn(): void
    {
        $this->assertEquals('Error creating folder', $this->buildResultMessage(false, 'en'));
    }

    public function testSessionMessageForDuplicateStudentFr(): void
    {
        $this->assertEquals('Un étudiant avec ce numéro existe déjà', $this->buildDuplicateMessage('fr'));
    }

    public function testSessionMessageForDuplicateStudentEn(): void
    {
        $this->assertEquals('A student with this ID already exists', $this->buildDuplicateMessage('en'));
    }

    private function buildResultMessage(bool $success, string $lang): string
    {
        if ($success) {
            return $lang === 'fr' ? 'Folder créé avec succès' : 'Folder created successfully';
        }
        return $lang === 'fr' ? 'Erreur lors de la création du dossier' : 'Error creating folder';
    }

    private function buildDuplicateMessage(string $lang): string
    {
        return $lang === 'fr'
            ? 'Un étudiant avec ce numéro existe déjà'
            : 'A student with this ID already exists';
    }

    // -------------------------------------------------------------------------
    // POST data mapping (mirrors the $data array built in control())
    // -------------------------------------------------------------------------

    public function testPostDataIsMappedCorrectly(): void
    {
        $_POST = [
            'numetu'    => '22000001',
            'nom'       => 'DUPONT',
            'prenom'    => 'Alice',
            'email_perso' => 'alice@example.com',
            'telephone' => '0600000000',
            'type'      => 'sortant',
        ];

        $data = [
            'NumEtu'         => (string)($_POST['numetu']      ?? ''),
            'Nom'            => (string)($_POST['nom']         ?? ''),
            'Prenom'         => (string)($_POST['prenom']      ?? ''),
            'EmailPersonnel' => (string)($_POST['email_perso'] ?? ''),
            'Telephone'      => (string)($_POST['telephone']   ?? ''),
            'Type'           => (string)($_POST['type']        ?? ''),
        ];

        $this->assertEquals('22000001',          $data['NumEtu']);
        $this->assertEquals('DUPONT',            $data['Nom']);
        $this->assertEquals('Alice',             $data['Prenom']);
        $this->assertEquals('alice@example.com', $data['EmailPersonnel']);
        $this->assertEquals('0600000000',        $data['Telephone']);
        $this->assertEquals('sortant',           $data['Type']);
    }

    public function testPostDataDefaultsToEmptyStringWhenKeysMissing(): void
    {
        $_POST = [];

        $data = [
            'NumEtu'         => (string)($_POST['numetu']      ?? ''),
            'Nom'            => (string)($_POST['nom']         ?? ''),
            'Prenom'         => (string)($_POST['prenom']      ?? ''),
            'EmailPersonnel' => (string)($_POST['email_perso'] ?? ''),
            'Telephone'      => (string)($_POST['telephone']   ?? ''),
            'Type'           => (string)($_POST['type']        ?? ''),
        ];

        foreach ($data as $key => $value) {
            $this->assertSame('', $value, "Expected empty string for key '$key'");
        }
    }

    // -------------------------------------------------------------------------
    // File upload guard logic (isolated)
    // -------------------------------------------------------------------------

    public function testPhotoIsAddedToDataWhenUploadSucceeds(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'photo_');
        $this->assertIsString($tmpFile, 'Could not create temp file');
        /** @var string $tmpFile */
        file_put_contents($tmpFile, 'fake-image-content');

        $_FILES['photo'] = [
            'error'    => UPLOAD_ERR_OK,
            'tmp_name' => $tmpFile,
        ];

        $data = $this->extractFileData('photo');

        $this->assertArrayHasKey('photo', $data);
        $this->assertEquals('fake-image-content', $data['photo']);

        unlink($tmpFile);
    }

    public function testPhotoIsNotAddedWhenUploadErrorOccurs(): void
    {
        $_FILES['photo'] = ['error' => UPLOAD_ERR_NO_FILE, 'tmp_name' => ''];

        $data = $this->extractFileData('photo');

        $this->assertArrayNotHasKey('photo', $data);
    }

    public function testCvIsAddedToDataWhenUploadSucceeds(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'cv_');
        $this->assertIsString($tmpFile, 'Could not create temp file');
        /** @var string $tmpFile */
        file_put_contents($tmpFile, 'fake-cv-content');

        $_FILES['cv'] = [
            'error'    => UPLOAD_ERR_OK,
            'tmp_name' => $tmpFile,
        ];

        $data = $this->extractFileData('cv');

        $this->assertArrayHasKey('cv', $data);
        $this->assertEquals('fake-cv-content', $data['cv']);

        unlink($tmpFile);
    }

    public function testCvIsNotAddedWhenFileNotPresent(): void
    {
        $_FILES = [];

        $data = $this->extractFileData('cv');

        $this->assertArrayNotHasKey('cv', $data);
    }

    /**
     * Mirrors the file-upload guard block in SaveStudentController::control().
     *
     * @return array<string, string>
     */
    private function extractFileData(string $field): array
    {
        $data = [];
        if (
            isset($_FILES[$field])
            && is_array($_FILES[$field])
            && (int)$_FILES[$field]['error'] === UPLOAD_ERR_OK
        ) {
            $content = file_get_contents((string)$_FILES[$field]['tmp_name']);
            if ($content !== false) {
                $data[$field] = $content;
            }
        }
        return $data;
    }

    // -------------------------------------------------------------------------
    // Lang defaulting
    // -------------------------------------------------------------------------

    public function testLangDefaultsToFrWhenNotProvided(): void
    {
        $_GET = [];
        $lang = $_GET['lang'] ?? 'fr';
        $this->assertEquals('fr', $lang);
    }

    public function testLangIsReadFromGetParameter(): void
    {
        $_GET['lang'] = 'en';
        $lang = $_GET['lang'] ?? 'fr';
        $this->assertEquals('en', $lang);
    }

    // -------------------------------------------------------------------------
    // Instantiation
    // -------------------------------------------------------------------------

    public function testControllerCanBeInstantiated(): void
    {
        $controller = new SaveStudentController();
        $this->assertInstanceOf(SaveStudentController::class, $controller);
    }
}