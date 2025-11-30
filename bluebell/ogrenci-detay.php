<?php
ob_start();
require_once 'dosyalar/config.php';
require_once 'dosyalar/Ydil.php';
require_once 'dosyalar/oturum.php';
$db = new Ydil();

if (!isset($_GET['id']) || $_GET['id'] === '') {
    header("Location: ogrenciler.php"); // listeye dön
    exit;
}
$ogr_no = $_GET['id'];

$sql = "
    SELECT 
        o.*,
        il.il_adi,
        ilce.ilce_adi
    FROM ogrenci1 o
    LEFT JOIN il ON il.il_id = o.il_id
    LEFT JOIN ilce ON ilce.ilce_id = o.ilce_id
    WHERE o.ogrenci_numara = :numara
    LIMIT 1
";

$ogrenci = $db->gets($sql, ['numara' => $ogr_no]);

$sql1 = "WITH s AS (
  SELECT s.sozlesme_id
  FROM sozlesme1 s
  JOIN ogrenci1  o ON o.ogrenci_id = s.ogrenci_id
  WHERE o.ogrenci_numara = :ogr_no
)
SELECT
  (SELECT COUNT(*) FROM s) AS sozlesme_sayisi,
  (SELECT COUNT(*) FROM taksit1 t JOIN s ON t.sozlesme_id = s.sozlesme_id) AS toplam_taksit,
  (SELECT COUNT(*) FROM taksit1 t JOIN s ON t.sozlesme_id = s.sozlesme_id
     WHERE COALESCE(t.odendi_tutar,0) >= t.tutar) AS odenen_taksit,
  (SELECT COUNT(*) FROM taksit1 t JOIN s ON t.sozlesme_id = s.sozlesme_id
     WHERE COALESCE(t.odendi_tutar,0) < t.tutar AND t.vade_tarihi < CURDATE()) AS gecikmis_taksit,
  (SELECT COUNT(*) FROM taksit1 t JOIN s ON t.sozlesme_id = s.sozlesme_id
     WHERE COALESCE(t.odendi_tutar,0) < t.tutar) AS kalan_taksit;
";

$sozlesme = $db->gets($sql1, [':ogr_no' => $ogr_no]);


print_r($sozlesme);
?>

<?php
$pageTitle = $ogrenci['ogrenci_adi']." ".$ogrenci['ogrenci_soyadi'];
$page_styles[] = ['href' => 'assets/css/dataTables.bootstrap5.min.css'];
require_once 'alanlar/header.php';
require_once 'alanlar/sidebar.php';
require_once 'ogrenci-detay-ortak.php';
?>


					<div class="col-xxl-9 col-xl-8">
						<div class="row">
							<div class="col-md-12">

								<!-- List -->
								<ul class="nav nav-tabs nav-tabs-bottom mb-4">
									<li>
										<a href="ogrenci-detay.php?id=<?= $ogr_no ?>" class="nav-link active"><i class="ti ti-school me-2"></i>Genel Bakış</a>
									</li>
									<!--<li>
										<a href="ogrenci-detay.php?id=<?= $ogr_no ?>" class="nav-link"><i class="ti ti-table-options me-2"></i>Time Table</a>
									</li>
									<li>
										<a href="ogrenci-detay.php?id=<?= $ogr_no ?>" class="nav-link"><i class="ti ti-calendar-due me-2"></i>Leave & Attendance</a>
									</li>
									<li>
										<a href="ogrenci-detay.php?id=<?= $ogr_no ?>" class="nav-link"><i class="ti ti-report-money me-2"></i>Fees</a>
									</li>
                                    <li>
										<a href="ogrenci-detay.php?id=<?= $ogr_no ?>" class="nav-link"><i class="ti ti-books me-2"></i>Library</a>
									</li>
                                    -->

									<li>
										<a href="ogrenci-detay-sozlesme.php?id=<?= $ogr_no ?>" class="nav-link"><i class="ti ti-bookmark-edit me-2"></i>Sözleşme Ve Taksitler</a>
									</li>

								</ul>
								<!-- /List -->

								<!-- Parents Information -->
								<div class="card">
									<div class="card-header">
										<h5>Parents Information</h5>
									</div>
									<div class="card-body">
										<div class="border rounded p-3 pb-0 mb-3">									
											<div class="row">									
												<div class="col-sm-6 col-lg-4">
													<div class="d-flex align-items-center mb-3">
														<span class="avatar avatar-lg flex-shrink-0">
															<img src="assets/img/parents/parent-13.jpg" class="img-fluid rounded"  alt="img">
														</span>
														<div class="ms-2 overflow-hidden">
															<h6 class="text-truncate">Jerald Vicinius</h6>
															<p class="text-primary">Father</p>
														</div>
													</div>
												</div>
												<div class="col-sm-6 col-lg-4">
													<div class="mb-3">
														<p class="text-dark fw-medium mb-1">Phone</p>
														<p>+1 45545 46464</p>
													</div>
												</div>
												<div class="col-sm-6 col-lg-4">
													<div class="d-flex align-items-center justify-content-between">
														<div class="mb-3 overflow-hidden me-3">												
															<p class="text-dark fw-medium mb-1">Email</p>
															<p class="text-truncate"><a href="https://preskool.dreamstechnologies.com/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="650f00170425001d04081509004b060a08">[email&#160;protected]</a></p>
														</div>
														<a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Print" data-bs-original-title="Reset Password" class="btn btn-dark btn-icon btn-sm mb-3"><i class="ti ti-lock-x"></i></a>
													</div>
												</div>
											</div>
										</div>
										<div class="border rounded p-3 pb-0 mb-3">									
											<div class="row">									
												<div class="col-lg-4 col-sm-6 ">
													<div class="d-flex align-items-center mb-3">
														<span class="avatar avatar-lg flex-shrink-0">
															<img src="assets/img/parents/parent-14.jpg" class="img-fluid rounded"  alt="img">
														</span>
														<div class="ms-2 overflow-hidden">
															<h6 class="text-truncate">Roberta Webber</h6>
															<p class="text-primary">Mother</p>
														</div>
													</div>
												</div>
												<div class="col-lg-4 col-sm-6 ">
													<div class="mb-3">
														<p class="text-dark fw-medium mb-1">Phone</p>
														<p>+1 46499 24357</p>
													</div>
												</div>
												<div class="col-lg-4 col-sm-6">
													<div class="d-flex align-items-center justify-content-between">
														<div class="mb-3 overflow-hidden me-3">												
															<p class="text-dark fw-medium mb-1">Email</p>
															<p class="text-truncate"><a href="https://preskool.dreamstechnologies.com/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="84f6ebe6e1c4e1fce5e9f4e8e1aae7ebe9">[email&#160;protected]</a></p>
														</div>
														<a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Print" data-bs-original-title="Reset Password" class="btn btn-dark btn-icon btn-sm mb-3"><i class="ti ti-lock-x"></i></a>
													</div>
												</div>
											</div>
										</div>
										<div class="border rounded p-3 pb-0">									
											<div class="row">									
												<div class="col-lg-4 col-sm-6">
													<div class="d-flex align-items-center mb-3">
														<span class="avatar avatar-lg flex-shrink-0">
															<img src="assets/img/parents/parent-13.jpg" class="img-fluid rounded"  alt="img">
														</span>
														<div class="ms-2 overflow-hidden">
															<h6 class="text-truncate">Jerald Vicinius</h6>
															<p class="text-primary">Gaurdian (Father)</p>
														</div>
													</div>
												</div>
												<div class="col-lg-4 col-sm-6">
													<div class="mb-3">
														<p class="text-dark fw-medium mb-1">Phone</p>
														<p>+1 45545 46464</p>
													</div>
												</div>
												<div class="col-lg-4 col-sm-6">
													<div class="d-flex align-items-center justify-content-between">
														<div class="mb-3 overflow-hidden me-3">												
															<p class="text-dark fw-medium mb-1">Email</p>
															<p class="text-truncate"><a href="https://preskool.dreamstechnologies.com/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="533936213213362b323e233f367d303c3e">[email&#160;protected]</a></p>
														</div>
														<a href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Print" data-bs-original-title="Reset Password" class="btn btn-dark btn-icon btn-sm mb-3"><i class="ti ti-lock-x"></i></a>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- /Parents Information -->

							</div>

							<!-- Documents -->
							<div class="col-xxl-6 d-flex">
								<div class="card flex-fill">
									<div class="card-header">
										<h5>Documents</h5>
									</div>
									<div class="card-body">
										<div class="bg-light-300 border rounded d-flex align-items-center justify-content-between mb-3 p-2">
											<div class="d-flex align-items-center overflow-hidden">
												<span class="avatar avatar-md bg-white rounded flex-shrink-0 text-default"><i class="ti ti-pdf fs-15"></i></span>
												<div class="ms-2">
													<p class="text-truncate fw-medium text-dark">BirthCertificate.pdf</p>
												</div>
											</div>
											<a href="student-details.html#" class="btn btn-dark btn-icon btn-sm"><i class="ti ti-download"></i></a>
										</div>
										<div class="bg-light-300 border rounded d-flex align-items-center justify-content-between p-2">
											<div class="d-flex align-items-center overflow-hidden">
												<span class="avatar avatar-md bg-white rounded flex-shrink-0 text-default"><i class="ti ti-pdf fs-15"></i></span>
												<div class="ms-2">
													<p class="text-truncate fw-medium text-dark">Transfer Certificate.pdf</p>
												</div>
											</div>
											<a href="student-details.html#" class="btn btn-dark btn-icon btn-sm"><i class="ti ti-download"></i></a>
										</div>
									</div>
								</div>
							</div>
							<!-- /Documents -->

							<!-- Address -->
							<div class="col-xxl-6 d-flex">
								<div class="card flex-fill">
									<div class="card-header">
										<h5>Address</h5>
									</div>
									<div class="card-body">
										<div class="d-flex align-items-center mb-3">
											<span class="avatar avatar-md bg-light-300 rounded me-2 flex-shrink-0 text-default"><i class="ti ti-map-pin-up"></i></span>
											<div>
												<p class="text-dark fw-medium mb-1">Current Address</p>
												<p>3495 Red Hawk Road, Buffalo Lake, MN 55314</p>
											</div>
										</div>
										<div class="d-flex align-items-center">
											<span class="avatar avatar-md bg-light-300 rounded me-2 flex-shrink-0 text-default"><i class="ti ti-map-pins"></i></span>
											<div>
												<p class="text-dark fw-medium mb-1">Permanent Address</p>
												<p>3495 Red Hawk Road, Buffalo Lake, MN 55314</p>
											</div>
										</div>
									</div>
								</div>								
							</div>
							<!-- /Address -->

							<!-- Previous School Details -->
							<div class="col-xxl-12">
								<div class="card">
									<div class="card-header">
										<h5>Previous School Details</h5>
									</div>
									<div class="card-body pb-1">
										<div class="row">
											<div class="col-md-6">
												<div class="mb-3">
													<p class="text-dark fw-medium mb-1">Previous School Name</p>
													<p>Oxford Matriculation, USA</p>
												</div>
											</div>
											<div class="col-md-6">
												<div class="mb-3">
													<p class="text-dark fw-medium mb-1">School Address</p>
													<p>1852 Barnes Avenue, Cincinnati, OH 45202</p>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- /Previous School Details -->

							<!-- Bank Details -->
							<div class="col-xxl-6 d-flex">
								<div class="card flex-fill">
									<div class="card-header">
										<h5>Bank Details</h5>
									</div>
									<div class="card-body pb-1">
										<div class="row">
											<div class="col-md-4">
												<div class="mb-3">
													<p class="text-dark fw-medium mb-1">Bank Name</p>
													<p>Bank of America</p>
												</div>
											</div>
											<div class="col-md-4">
												<div class="mb-3">
													<p class="text-dark fw-medium mb-1">Branch</p>
													<p>Cincinnati</p>
												</div>
											</div>
											<div class="col-md-4">
												<div class="mb-3">
													<p class="text-dark fw-medium mb-1">IFSC</p>
													<p>BOA83209832</p>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- /Bank Details -->

							<!-- Medical History -->
							<div class="col-xxl-6 d-flex">
								<div class="card flex-fill">
									<div class="card-header">
										<h5>Medical History</h5>
									</div>
									<div class="card-body pb-1">
										<div class="row">
											<div class="col-md-6">
												<div class="mb-3">
													<p class="text-dark fw-medium mb-1">Known Allergies</p>
													<span class="badge bg-light text-dark">Rashes</span>
												</div>
											</div>
											<div class="col-md-6">
												<div class="mb-3">
													<p class="text-dark fw-medium mb-1">Medications</p>
													<p>-</p>
												</div>
											</div>
										</div>
									</div>
								</div>								
							</div>
							<!-- /Medical History -->

							<!-- Other Info -->
							<div class="col-xxl-12">
								<div class="card">
									<div class="card-header">
										<h5>Other Info</h5>
									</div>
									<div class="card-body">
										<p>Depending on the specific needs of your organization or system, additional information may be collected or tracked. It's important to ensure that any data collected complies with privacy regulations and policies to protect students' sensitive information.</p>
									</div>
								</div>
							</div>
							<!-- /Other Info -->

						</div>
					</div>

				</div>
			</div>
		</div>
		<!-- /Page Wrapper -->

		<!-- Login Details -->
		<div class="modal fade" id="login_detail">
			<div class="modal-dialog modal-dialog-centered  modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Login Details</h4>
						<button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
							<i class="ti ti-x"></i>
						</button>
					</div>		
					<div class="modal-body">		
						<div class="student-detail-info">
							<span class="student-img"><img src="assets/img/students/student-01.jpg" alt="Img"></span>
							<div class="name-info">
								<h6>Janet <span>III, A</span></h6>
							</div>
						</div>
						<div class="table-responsive custom-table no-datatable_length">
							<table class="table datanew">
								<thead class="thead-light">
									<tr>
										<th>User Type</th>
										<th>User Name</th>
										<th>Password </th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>Parent</td>
										<td>parent53</td>
										<td>parent@53</td>
									</tr>
									<tr>
										<td>Student</td>
										<td>student20</td>
										<td>stdt@53</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>			
					<div class="modal-footer">
						<a href="student-details.html#" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</a>
					</div>
				</div>
			</div>
		</div>
		<!-- /Login Details -->	

		<!-- Add Fees Collect -->
		<div class="modal fade" id="add_fees_collect">
			<div class="modal-dialog modal-dialog-centered  modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<div class="d-flex align-items-center">
							<h4 class="modal-title">Collect Fees</h4>
							<spa class="badge badge-sm bg-primary ms-2">AD124556</span>
						</div>
						<button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
							<i class="ti ti-x"></i>
						</button>
					</div>		
					<form action="collect-fees.html">
						<div class="modal-body">
							<div class="bg-light-300 p-3 pb-0 rounded mb-4">
								<div class="row align-items-center">
									<div class="col-lg-3 col-md-6">
										<div class="d-flex align-items-center mb-3">
											<a href="student-details.html" class="avatar avatar-md me-2">
												<img src="assets/img/students/student-01.jpg" alt="img">
											</a>
											<a href="student-details.html" class="d-flex flex-column"><span class="text-dark">Janet</span>III, A</a>
										</div>
									</div>
									<div class="col-lg-3 col-md-6">
										<div class="mb-3">
											<span class="fs-12 mb-1">Total Outstanding</span>
											<p class="text-dark">2000</p>
										</div>
									</div>
									<div class="col-lg-3 col-md-6">
										<div class="mb-3">
											<span class="fs-12 mb-1">Last Date</span>
											<p class="text-dark">25 May 2024</p>
										</div>
									</div>
									<div class="col-lg-3 col-md-6">
										<div class="mb-3">
											<span class="badge badge-soft-danger"><i
											class="ti ti-circle-filled me-2"></i>Unpaid</span>
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-6">
									<div class="mb-3">
										<label class="form-label">Fees Group</label>
										<select class="select">
											<option>Select</option>
											<option>Class 1 General</option>
											<option>Monthly Fees</option>
											<option>Admission-Fees</option>
											<option>Class 1- I Installment</option>
										</select>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="mb-3">
										<label class="form-label">Fees Type</label>
										<select class="select">
											<option>Select</option>
											<option>Tuition Fees</option>
											<option>Monthly Fees</option>
											<option>Admission Fees</option>
											<option>Bus Fees</option>
										</select>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="mb-3">
										<label class="form-label">Amount</label>
										<input type="text" class="form-control" placeholder="Enter Amout">
									</div>
								</div>
								<div class="col-lg-6">
									<div class="mb-3">
										<label class="form-label">Collection Date</label>
										<div class="date-pic">
											<input type="text" class="form-control datetimepicker"
												placeholder="Select">
											<span class="cal-icon"><i class="ti ti-calendar"></i></span>
										</div>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="mb-3">
										<label class="form-label">Payment Type</label>
										<select class="select">
											<option>Select</option>
											<option>Paytm</option>
											<option>Cash On Delivery</option>
										</select>
									</div>
								</div>
								<div class="col-lg-6">
									<div class="mb-3">
										<label class="form-label">Payment Reference No</label>
										<input type="text" class="form-control" placeholder="Enter Payment Reference No">
									</div>
								</div>
								<div class="col-lg-12">
									<div class="d-flex align-items-center justify-content-between">
										<div class="status-title">
											<h5>Status</h5>
											<p>Change the Status by toggle </p>
										</div>
										<div class="form-check form-switch">
											<input class="form-check-input" type="checkbox" role="switch" id="switch-sm2">
										</div>
									</div>
								</div>
								<div class="col-lg-12">
									<div class="mb-0">
										<label class="form-label">Notes</label>
										<textarea rows="4" class="form-control" placeholder="Add Notes"></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<a href="student-details.html#" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</a>
							<button type="submit" class="btn btn-primary">Pay Fees</button>
						</div>
					</form>	
				</div>
			</div>
		</div>
		<!-- Add Fees Collect -->

	</div>
	<!-- /Main Wrapper -->

	<!-- jQuery -->

    <script src="assets/js/jquery-3.7.1.min.js" type="text/javascript"></script>
	<script src="assets/js/bootstrap.bundle.min.js" type="text/javascript"></script>
	<script src="assets/js/moment.js" type="text/javascript"></script>
	<script src="assets/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
	<script src="assets/js/feather.min.js" type="text/javascript"></script>
	<script src="assets/js/jquery.slimscroll.min.js" type="text/javascript"></script>
	<script src="assets/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
	<script src="assets/plugins/select2/js/select2.min.js" type="text/javascript"></script>
	<script src="assets/js/jquery.dataTables.min.js" type="text/javascript"></script>
	<script src="assets/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
	<script src="assets/plugins/theia-sticky-sidebar/ResizeSensor.js" type="text/javascript"></script>
	<script src="assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js" type="text/javascript"></script>
	<script src="assets/js/script.js" type="text/javascript"></script>

</body>
</html>