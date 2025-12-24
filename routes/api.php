<?php

use App\Http\Controllers\AdminControllers\AdminController;
use App\Http\Controllers\api\CalculationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\AcademicControllers\ComAcademicYearsController;
use App\Http\Controllers\CommonControllers\AssigneeLevelController;
use App\Http\Controllers\CommonControllers\ComPermissionController;
use App\Http\Controllers\CommonControllers\DepartmentController;
use App\Http\Controllers\CommonControllers\FactoryController;
use App\Http\Controllers\CommonControllers\JobPositionController;
use App\Http\Controllers\CommonControllers\OrganizationController;
use App\Http\Controllers\CommonControllers\PersonTypeController;
use App\Http\Controllers\CommonControllers\ResponsibleSectionController;
use App\Http\Controllers\CommonControllers\UserTypeController;
use App\Http\Controllers\SubjectControllers\ComSubjectsController;
use App\Http\Controllers\ClassMngControllers\ComClassMngController;
use App\Http\Controllers\ComStudentProfileController\ComStudentProfileController;
use App\Http\Controllers\ComTeacherProfile\ComTeacherProfileController;
use App\Http\Controllers\GradesControllers\ComGradesController;
use App\Http\Controllers\HealthAndSaftyControllers\AiAccidentCategoryController;
use App\Http\Controllers\HealthAndSaftyControllers\AiAccidentInjuryTypeController;
use App\Http\Controllers\HealthAndSaftyControllers\AiAccidentRecordController;
use App\Http\Controllers\HealthAndSaftyControllers\AiAccidentTypeController;
use App\Http\Controllers\HealthAndSaftyControllers\AiIncidentCircumstancesController;
use App\Http\Controllers\HealthAndSaftyControllers\AiIncidentFactorsController;
use App\Http\Controllers\HealthAndSaftyControllers\AiIncidentRecodeController;
use App\Http\Controllers\HealthAndSaftyControllers\AiIncidentTypeOfConcernController;
use App\Http\Controllers\HealthAndSaftyControllers\AiIncidentTypeOfNearMissController;
use App\Http\Controllers\HealthAndSaftyControllers\ClinicalSuiteRecodeController;
use App\Http\Controllers\HealthAndSaftyControllers\CsConsultingDoctorController;
use App\Http\Controllers\HealthAndSaftyControllers\CsDesignationController;
use App\Http\Controllers\HealthAndSaftyControllers\CsMedicineStockController;
use App\Http\Controllers\HealthAndSaftyControllers\DocumentDocumentTypeController;
use App\Http\Controllers\HealthAndSaftyControllers\DocumentRecodeController;
use App\Http\Controllers\HealthAndSaftyControllers\HazardAndRiskController;
use App\Http\Controllers\HealthAndSaftyControllers\HrCategoryController;
use App\Http\Controllers\HealthAndSaftyControllers\HrDivisionController;
use App\Http\Controllers\HealthAndSaftyControllers\HsOcMrMdDocumentTypeController;
use App\Http\Controllers\HealthAndSaftyControllers\OhMiPiMedicineInventoryController;
use App\Http\Controllers\HealthAndSaftyControllers\OhMiPiMiSupplierNameController;
use App\Http\Controllers\HealthAndSaftyControllers\OhMiPiMiSupplierTypeController;
use App\Http\Controllers\HealthAndSaftyControllers\OhMrBeBenefitTypeController;
use App\Http\Controllers\HealthAndSaftyControllers\OhMrBenefitRequestController;
use App\Http\Controllers\HealthAndSaftyControllers\OsMiMedicineNameController;
use App\Http\Controllers\HealthAndSaftyControllers\OsMiMedicineNameFormController;
use App\Http\Controllers\HealthAndSaftyControllers\OsMiMedicineRequestController;
use App\Http\Controllers\HealthAndSaftyControllers\OsMiMedicineTypeController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\SocialApps\SaArEmploymentClassificationController;
use App\Http\Controllers\SocialApps\SaArResignationTypeController;
use App\Http\Controllers\SocialApps\SaAttritionRecordController;
use App\Http\Controllers\SocialApps\SaGrCategoryController;
use App\Http\Controllers\SocialApps\SaGrChannelController;
use App\Http\Controllers\SocialApps\SaGrievanceRecodeController;
use App\Http\Controllers\SocialApps\SaGrSubmissionsController;
use App\Http\Controllers\SocialApps\SaGrTopicController;
use App\Http\Controllers\SocialApps\SaRagRecodeController;
use App\Http\Controllers\SocialApps\SaRrCategoryController;
use App\Http\Controllers\SocialApps\SaRrDesignationNameController;
use App\Http\Controllers\SocialApps\SaRrEmployeeTypeController;
use App\Http\Controllers\SocialApps\SaRrEmploymentTypeController;
use App\Http\Controllers\SocialApps\SaRrFunctionController;
use App\Http\Controllers\SocialApps\SaRrSourceOfHirngController;
use App\Http\Controllers\SustainabilityAppsControllers\SaAiExternalAuditCategoryController;
use App\Http\Controllers\SustainabilityAppsControllers\SaAiExternalAuditFirmController;
use App\Http\Controllers\SustainabilityAppsControllers\SaAiExternalAuditRecodeController;
use App\Http\Controllers\SustainabilityAppsControllers\SaAiExternalAuditStandardController;
use App\Http\Controllers\SustainabilityAppsControllers\SaAiExternalAuditTypeController;
use App\Http\Controllers\SustainabilityAppsControllers\SaAiIaAuditTitleController;
use App\Http\Controllers\SustainabilityAppsControllers\SaAiIaAuditTypeController;
use App\Http\Controllers\SustainabilityAppsControllers\SaAiIaContactPersonController;
use App\Http\Controllers\SustainabilityAppsControllers\SaAiIaInternalAuditeeController;
use App\Http\Controllers\SustainabilityAppsControllers\SaAiIaProcessTypeController;
use App\Http\Controllers\SustainabilityAppsControllers\SaAiIaQuestionRecodeController;
use App\Http\Controllers\SustainabilityAppsControllers\SaAiIaSuplierTypeController;
use App\Http\Controllers\SustainabilityAppsControllers\SaAiInternalAuditFactoryController;
use App\Http\Controllers\SustainabilityAppsControllers\SaAiInternalAuditRecodeController;
use App\Http\Controllers\SustainabilityAppsControllers\SaCmChemicalFormTypeController;
use App\Http\Controllers\SustainabilityAppsControllers\SaCmChemicalManagementRecodeController;
use App\Http\Controllers\SustainabilityAppsControllers\SaCmCmrCommercialNameController;
use App\Http\Controllers\SustainabilityAppsControllers\SaCmCmrHazardTypeController;
use App\Http\Controllers\SustainabilityAppsControllers\SaCmCmrProductStandardController;
use App\Http\Controllers\SustainabilityAppsControllers\SaCmCmrUseOfPPEController;
use App\Http\Controllers\SustainabilityAppsControllers\SaCmCmrZdhcCategoryController;
use App\Http\Controllers\SustainabilityAppsControllers\SaCmPirPositiveListController;
use App\Http\Controllers\SustainabilityAppsControllers\SaCmPirSuplierNameController;
use App\Http\Controllers\SustainabilityAppsControllers\SaCmPirTestingLabController;
use App\Http\Controllers\SustainabilityAppsControllers\SaCmPurchaseInventoryRecodeController;
use App\Http\Controllers\SustainabilityAppsControllers\SaEmrConsumptionCategoryController;
use App\Http\Controllers\SustainabilityAppsControllers\SaEnvirementManagementRecodeController;
use App\Http\Controllers\SustainabilityAppsControllers\SaEnvirementTargetSettingRecodeController;
use App\Http\Controllers\SustainabilityAppsControllers\SaETsCategoryController;
use App\Http\Controllers\SustainabilityAppsControllers\SaETsSourceController;
use App\Http\Controllers\SustainabilityAppsControllers\SaSrAdditionalSDGController;
use App\Http\Controllers\SustainabilityAppsControllers\SaSrAlignmentSDGController;
use App\Http\Controllers\SustainabilityAppsControllers\SaSrIdImpactTypeController;
use App\Http\Controllers\SustainabilityAppsControllers\SaSrMaterialityIssuesController;
use App\Http\Controllers\SustainabilityAppsControllers\SaSrMaterialityTypeController;
use App\Http\Controllers\SustainabilityAppsControllers\SaSrPillarsController;
use App\Http\Controllers\SustainabilityAppsControllers\SaSrSDGController;

use App\Http\Controllers\SustainabilityAppsControllers\SaSrSDGReportingRecodeController;
use App\Http\Controllers\StudentMarksController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClassReportController;
use Illuminate\Support\Facades\Route;

Route::post('calculate', [CalculationController::class, 'store']);
Route::post('register', [RegisteredUserController::class, 'store']);

Route::get('all-users', [UserController::class, 'index']);
Route::get('users/search', [UserController::class, 'search']);

Route::post('login', [LoginController::class, 'login']);
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('reset-password', [ForgotPasswordController::class, 'otpVerifyFunction']);
Route::post('change-password', [ForgotPasswordController::class, 'changePassword']);
Route::get('organizations', [OrganizationController::class, 'index']);

Route::middleware('auth:sanctum')->get('user', [UserController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('organizations/{id}/update', [OrganizationController::class, 'update']);
    Route::delete('organizations/{id}/delete', [OrganizationController::class, 'destroy']);

    Route::get('users-assignee', [UserController::class, 'assignee']);
    Route::post('user-change-password', [UserController::class, 'changePassword']);
    Route::post('user/{id}/profile-update', [UserController::class, 'profileUpdate']);
    Route::post('user/{id}/email-change', [UserController::class, 'emailChangeInitiate']);
    Route::post('user/{id}/email-change-verify', [UserController::class, 'emailChangeVerify']);
    Route::post('user/{id}/email-change-confirm', [UserController::class, 'emailChangeConfirm']);

    Route::get('users', [AdminController::class, 'index']);
    Route::post('users/{id}/update', [AdminController::class, 'update']);
    Route::get('users-assignee-level', [AdminController::class, 'assigneeLevel']);
});

Route::middleware('auth:sanctum')->group(function () {


    Route::get('chemical-records', [SaCmChemicalManagementRecodeController::class, 'index']);
    Route::post('chemical-records', [SaCmChemicalManagementRecodeController::class, 'store']);
    Route::post('chemical-records/{id}/update', [SaCmChemicalManagementRecodeController::class, 'update']);
    Route::delete('chemical-records/{id}/delete', [SaCmChemicalManagementRecodeController::class, 'destroy']);
    Route::get('chemical-records-assignee', [SaCmChemicalManagementRecodeController::class, 'assignee']);
    Route::post('chemical-records/{id}/approve', [SaCmChemicalManagementRecodeController::class, 'approvedStatus']);

    Route::get('purchase-inventory-records', [SaCmPurchaseInventoryRecodeController::class, 'index']);
    Route::post('purchase-inventory-records/{id}/update', [SaCmPurchaseInventoryRecodeController::class, 'update']);
    Route::post('purchase-inventory-records/{id}/publish-update', [SaCmPurchaseInventoryRecodeController::class, 'publishStatus']);
    Route::delete('purchase-inventory-record/{id}/delete', [SaCmPurchaseInventoryRecodeController::class, 'destroy']);
    Route::get('chemical-transaction-published', [SaCmPurchaseInventoryRecodeController::class, 'getPublishedStatus']);
    Route::get('purchase-inventory-records-assign-task', [SaCmPurchaseInventoryRecodeController::class, 'assignTask']);
    Route::get('purchase-inventory-records-assign-task-approved', [SaCmPurchaseInventoryRecodeController::class, 'assignTaskApproved']);
    Route::post('purchase-inventory-records/{id}/approve', [SaCmPurchaseInventoryRecodeController::class, 'updateStatusToApproved']);

    Route::get('chemical-dashboard/{startDate}/{endDate}/{division}/stock-amount', [SaCmPurchaseInventoryRecodeController::class, 'getStockAmount']);
    Route::get('chemical-dashboard/{startDate}/{endDate}/{division}/monthly-delivery', [SaCmPurchaseInventoryRecodeController::class, 'getMonthlyDelivery']);
    Route::get('chemical-dashboard/{startDate}/{endDate}/{division}/latest-record', [SaCmPurchaseInventoryRecodeController::class, 'getLatestRecord']);
    Route::get('chemical-dashboard/{startDate}/{endDate}/{division}/transaction-latest-record', [SaCmPurchaseInventoryRecodeController::class, 'getTransactionLatestRecord']);
    Route::get('chemical-dashboard/{startDate}/{endDate}/{division}/stock-threshold', [SaCmPurchaseInventoryRecodeController::class, 'getStockThreshold']);
    Route::get('chemical-dashboard/{startDate}/{endDate}/{division}/highest-stock', [SaCmPurchaseInventoryRecodeController::class, 'getHighestStock']);
    Route::get('chemical-dashboard/{startDate}/{endDate}/{division}/status-summary', [SaCmPurchaseInventoryRecodeController::class, 'getStatusSummary']);
    Route::get('chemical-dashboard/{startDate}/{endDate}/{division}/chemical-inventory-insights', [SaCmPurchaseInventoryRecodeController::class, 'getChemicalInventoryInsights']);
    Route::get('chemical-dashboard/{Year}/all-summary', [SaCmPurchaseInventoryRecodeController::class, 'getAllSummary']);
    Route::get('chemical-dashboard/{startDate}/{endDate}/{division}/category-and-classification', [SaCmPurchaseInventoryRecodeController::class, 'getCategoryAndClassification']);
    Route::get('chemical-dashboard/{startDate}/{endDate}/{division}/do-you-have-msds', [SaCmPurchaseInventoryRecodeController::class, 'getDoYouHaveMsdsPercentage']);
});

Route::get('user-permissions', [ComPermissionController::class, 'index']);
Route::post('user-permissions', [ComPermissionController::class, 'store']);
Route::get('user-permissions/{id}/show', [ComPermissionController::class, 'show']);
Route::post('user-permissions/{id}/update', [ComPermissionController::class, 'update']);
Route::delete('user-permissions/{id}/delete', [ComPermissionController::class, 'destroy']);

Route::get('responsible-section', [ResponsibleSectionController::class, 'index']);
Route::post('responsible-section', [ResponsibleSectionController::class, 'store']);

Route::get('assignee-level', [AssigneeLevelController::class, 'index']);

Route::get('job-positions', [JobPositionController::class, 'index']);
Route::post('job-positions', [JobPositionController::class, 'store']);

Route::get('user-types', [UserTypeController::class, 'index']);
Route::post('user-types', [UserTypeController::class, 'store']);

Route::post('departments', [DepartmentController::class, 'store']);
Route::get('departments', [DepartmentController::class, 'index']);

Route::get('factory', [FactoryController::class, 'show']);
Route::post('factory', [FactoryController::class, 'store']);

Route::get('person-types', [PersonTypeController::class, 'index']);
Route::post('person-types', [PersonTypeController::class, 'store']);

Route::get('consumption-categories', [SaEmrConsumptionCategoryController::class, 'index']);
Route::post('consumption-categories', [SaEmrConsumptionCategoryController::class, 'store']);
Route::get('consumption-get-categories', [SaEmrConsumptionCategoryController::class, 'getcategories']);
Route::get('consumption-get/{categoryName}/units', [SaEmrConsumptionCategoryController::class, 'getUnit']);
Route::get('consumption-get/{categoryName}/sources', [SaEmrConsumptionCategoryController::class, 'getSource']);

Route::get('commercial-names', [SaCmCmrCommercialNameController::class, 'index']);
Route::post('commercial-names', [SaCmCmrCommercialNameController::class, 'store']);

Route::get('chemical-supplier-names', [SaCmPirSuplierNameController::class, 'index']);
Route::post('chemical-supplier-names', [SaCmPirSuplierNameController::class, 'store']);

Route::get('chemical-form-types', [SaCmChemicalFormTypeController::class, 'index']);
Route::post('chemical-form-types', [SaCmChemicalFormTypeController::class, 'store']);

Route::get('zdhc-categories', [SaCmCmrZdhcCategoryController::class, 'index']);
Route::post('zdhc-categories', [SaCmCmrZdhcCategoryController::class, 'store']);

Route::get('product-standard', [SaCmCmrProductStandardController::class, 'index']);
Route::post('product-standard', [SaCmCmrProductStandardController::class, 'store']);

Route::get('hazard-types', [SaCmCmrHazardTypeController::class, 'index']);
Route::post('hazard-types', [SaCmCmrHazardTypeController::class, 'store']);

Route::get('use-of-ppes', [SaCmCmrUseOfPPEController::class, 'index']);
Route::post('use-of-ppes', [SaCmCmrUseOfPPEController::class, 'store']);

Route::get('testing-labs', [SaCmPirTestingLabController::class, 'index']);
Route::post('testing-labs', [SaCmPirTestingLabController::class, 'store']);

Route::get('positive-list', [SaCmPirPositiveListController::class, 'index']);
Route::post('positive-list', [SaCmPirPositiveListController::class, 'store']);

Route::get('image/{imageId}', [ImageUploadController::class, 'getImage']);
Route::post('upload', [ImageUploadController::class, 'uploadImage']);
Route::delete('image/{imageId}', [ImageUploadController::class, 'deleteImage']);
Route::post('image/update/{imageId}', [ImageUploadController::class, 'updateImage']);

// HR Divisions
Route::get('hr-divisions', [HrDivisionController::class, 'index']);
Route::post('hr-divisions', [HrDivisionController::class, 'store']);

// Supplier Types (OH MI PI)
Route::get('supplier-type', [OhMiPiMiSupplierTypeController::class, 'index']);
Route::post('supplier-type', [OhMiPiMiSupplierTypeController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('grade', [ComGradesController::class, 'store']);
    Route::get('grade', [ComGradesController::class, 'index']);
    Route::post('grade/{gradeId}', [ComGradesController::class, 'update']);
    Route::delete('grade/{gradeId}', [ComGradesController::class, 'destroy']);

    Route::post('year', [ComAcademicYearsController::class, 'store']);
    Route::get('year', [ComAcademicYearsController::class, 'index']);
    Route::post('year/{yearId}', [ComAcademicYearsController::class, 'update']);
    Route::delete('year/{yearId}', [ComAcademicYearsController::class, 'destroy']);

    Route::post('subject', [ComSubjectsController::class, 'store']);
    Route::get('subject', [ComSubjectsController::class, 'index']);
    Route::post('subject/{subjectId}', [ComSubjectsController::class, 'update']);
    Route::delete('subject/{subjectId}', [ComSubjectsController::class, 'destroy']);
    Route::get('all-subjects', [ComSubjectsController::class, 'getSubjects']);

    // Classes (ComClassMng)
    Route::post('class', [ComClassMngController::class, 'store']);
    Route::get('class', [ComClassMngController::class, 'index']);
    Route::get('class/{id}', [ComClassMngController::class, 'show']);
    Route::post('class/{id}', [ComClassMngController::class, 'update']);
    Route::delete('class/{id}', [ComClassMngController::class, 'destroy']);

    // Teacher profiles
    Route::get('teacher-profiles', [ComTeacherProfileController::class, 'index']);
    Route::post('teacher-profiles', [ComTeacherProfileController::class, 'store']);
    Route::get('teacher-profiles/{id}', [ComTeacherProfileController::class, 'show']);
    Route::post('teacher-profiles/{id}', [ComTeacherProfileController::class, 'update']);
    Route::delete('teacher-profiles/{id}', [ComTeacherProfileController::class, 'destroy']);
    Route::get('teacher-years', [ComTeacherProfileController::class, 'getTeacherYears']);
    Route::get('teacher-mediums/{year}', [ComTeacherProfileController::class, 'getTeacherMediums']);
    Route::get('teacher-grades/{year}', [ComTeacherProfileController::class, 'getTeacherGrades']);
    Route::get('teacher-class/{year}/{grade}', [ComTeacherProfileController::class, 'getTeacherClasses']);
    Route::get('teacher-subject/{year}/{grade}/{class}/{medium}/subject', [ComTeacherProfileController::class, 'getTeacherSubject']);



    // Student profiles
    Route::get('student-profiles', [ComStudentProfileController::class, 'index']);
    Route::post('student-profiles', [ComStudentProfileController::class, 'store']);
    Route::get('student-profiles/{id}', [ComStudentProfileController::class, 'show']);
    Route::post('student-profiles/{id}', [ComStudentProfileController::class, 'update']);
    Route::delete('student-profiles/{id}', [ComStudentProfileController::class, 'destroy']);
    Route::get('student-profiles/{gradeId}/{classId}/{year}/{medium}/{subjectId}/{term}/marks', [ComStudentProfileController::class, 'getStudentMarks']);

    // Student marks
    Route::get('student-marks', [StudentMarksController::class, 'index']);
    Route::post('student-marks', [StudentMarksController::class, 'store']);
    Route::get('student-marks/{id}', [StudentMarksController::class, 'show']);
    Route::post('student-marks/{id}', [StudentMarksController::class, 'update']);
    Route::delete('student-marks/{id}', [StudentMarksController::class, 'destroy']);

    Route::get('class/{year}/{grade}/{class}/{examType}/report', [ClassReportController::class, 'getClassReport']);
});
