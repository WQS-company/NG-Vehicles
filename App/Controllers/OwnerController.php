<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use App\Models\Owner;
use App\Models\DynamicField;
use App\Models\AuditLog;
use App\Models\ActivityLog;

class OwnerController extends Controller {

    public function __construct() {
        Auth::requireAuth();
        if (Auth::role() !== ROLE_SUPER_ADMIN) {
            Auth::requireFeature('registration');
        }
    }

    public function register() {
        $db = Database::getInstance();
        $dfModel = new DynamicField();
        
        $error = null;
        $success = null;

        $dynamicFields = $dfModel->getFieldsForEntity('owner');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!$this->validateCsrfToken($token)) {
                $error = 'Invalid CSRF token.';
            } else {
                $fullName = trim($_POST['full_name'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $dob = trim($_POST['date_of_birth'] ?? '');
                $gender = $_POST['gender'] ?? 'Male';
                $nationality = trim($_POST['nationality'] ?? 'Nigeria');
                $occupation = trim($_POST['occupation'] ?? '');
                $nin = trim($_POST['nin'] ?? '');
                $bvn = trim($_POST['bvn'] ?? '');
                $address = trim($_POST['address'] ?? '');
                $state = trim($_POST['state'] ?? '');
                $lga = trim($_POST['lga'] ?? '');

                // Biometrics data (base64 from webcam or signature pad)
                $webcamImageBase64 = $_POST['webcam_image'] ?? '';
                $signatureImageBase64 = $_POST['signature_image'] ?? '';

                // Uniqueness validations
                $existingPhone = $db->fetch("SELECT id FROM owners WHERE phone = :phone LIMIT 1", ['phone' => $phone]);
                $existingEmail = $db->fetch("SELECT id FROM owners WHERE email = :email LIMIT 1", ['email' => $email]);

                if (empty($fullName) || empty($phone) || empty($email) || empty($nin)) {
                    $error = 'Full Name, Phone, Email, and NIN are mandatory.';
                } elseif ($existingPhone) {
                    $error = 'An owner with this Phone Number already exists.';
                } elseif ($existingEmail) {
                    $error = 'An owner with this Email already exists.';
                } else {
                    // Upload/Save Passport Photo (Webcam base64 or file upload)
                    $passportPhotoPath = null;
                    if (!empty($webcamImageBase64)) {
                        // Decode base64
                        $img = str_replace('data:image/jpeg;base64,', '', $webcamImageBase64);
                        $img = str_replace('data:image/png;base64,', '', $img);
                        $img = str_replace(' ', '+', $img);
                        $data = base64_decode($img);
                        
                        $fileName = md5(time() . $fullName) . '.jpg';
                        $uploadDir = BASE_PATH . '/public/uploads/owners/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        
                        if (file_put_contents($uploadDir . $fileName, $data)) {
                            $passportPhotoPath = 'public/uploads/owners/' . $fileName;
                        }
                    } elseif (isset($_FILES['passport_photo']) && $_FILES['passport_photo']['error'] === UPLOAD_ERR_OK) {
                        $fileTmpPath = $_FILES['passport_photo']['tmp_name'];
                        $fileName = $_FILES['passport_photo']['name'];
                        $fileSize = $_FILES['passport_photo']['size'];
                        
                        $fileNameCmps = explode(".", $fileName);
                        $fileExtension = strtolower(end($fileNameCmps));
                        
                        $allowedExtensions = ['jpg', 'jpeg', 'png'];
                        if (in_array($fileExtension, $allowedExtensions) && $fileSize < 3000000) {
                            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                            $uploadDir = BASE_PATH . '/public/uploads/owners/';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0777, true);
                            }
                            
                            if (move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                                $passportPhotoPath = 'public/uploads/owners/' . $newFileName;
                            }
                        } else {
                            $error = 'Invalid passport image type or size (Max 3MB, JPG/PNG only).';
                        }
                    }

                    // Upload/Save Signature (Base64 signature canvas)
                    $signaturePath = null;
                    if (!empty($signatureImageBase64)) {
                        $img = str_replace('data:image/png;base64,', '', $signatureImageBase64);
                        $img = str_replace(' ', '+', $img);
                        $data = base64_decode($img);
                        
                        $sigFileName = 'sig_' . md5(time() . $fullName) . '.png';
                        $sigDir = BASE_PATH . '/public/uploads/signatures/';
                        if (!is_dir($sigDir)) {
                            mkdir($sigDir, 0777, true);
                        }
                        
                        if (file_put_contents($sigDir . $sigFileName, $data)) {
                            $signaturePath = 'public/uploads/signatures/' . $sigFileName;
                        }
                    }

                    if (!$error) {
                        // Gather dynamic fields values
                        $customFieldsValues = [];
                        foreach ($dynamicFields as $field) {
                            $fieldName = $field['field_name'];
                            $val = $_POST['custom_' . $field['id']] ?? '';
                            
                            if ($field['is_required'] && empty($val)) {
                                $error = "Custom field '{$fieldName}' is required.";
                                break;
                            }
                            $customFieldsValues[$fieldName] = $val;
                        }

                        if (!$error) {
                            // Insert into database
                            $ownerId = $db->insert('owners', [
                                'full_name' => $fullName,
                                'phone' => $phone,
                                'email' => $email,
                                'date_of_birth' => $dob,
                                'gender' => $gender,
                                'nationality' => $nationality,
                                'occupation' => $occupation,
                                'nin' => $nin,
                                'bvn' => $bvn,
                                'address' => $address,
                                'state' => $state,
                                'lga' => $lga,
                                'passport_photo_path' => $passportPhotoPath,
                                'signature_path' => $signaturePath,
                                'custom_fields' => json_encode($customFieldsValues)
                            ]);

                            AuditLog::log($_SESSION['user_id'], "Registered new owner Profile: {$fullName} (ID: {$ownerId})");
                            ActivityLog::log("New owner profile created successfully: {$fullName}");
                            
                            $success = 'Owner profile and biometric records created successfully.';
                        }
                    }
                }
            }
        }

        $csrfToken = $this->generateCsrfToken();
        $this->render('owners/register', [
            'title' => 'Register New Owner',
            'activePage' => 'owner_reg',
            'dynamicFields' => $dynamicFields,
            'error' => $error,
            'success' => $success,
            'csrfToken' => $csrfToken
        ]);
    }
}
