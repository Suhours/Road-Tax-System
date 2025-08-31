

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>


<?php

include './sidebar.php'

?>



<style>
    body {
        /* padding: 20px; */
        background-color: #f8f9fa;
    }
    .header {
        margin-bottom: 30px;
    }
    .copyright {
        margin-top: 30px;
        text-align: center;
        font-size: 0.9rem;
        color: #6c757d;
    }
    .actions i {
        margin: 0 5px;
        cursor: pointer;
    }
    .search-box {
        margin-bottom: 20px;
      
        
    }

    #statement_output p {
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
    }

    #payment_summary td {
        font-size: 1rem;
    }
</style>



<div class="col-lg-10 py-3">
        
<div class="container" style="margin-top: 100px;">
<div class="pc-container bg-white p-4 rounded shadow-sm">
      <div class="pc-content">
        <!-- [ breadcrumb ] start -->

<div class="container my-5">
  <h3 class="mb-4 text-primary fw-bold">Vehicle Payment Statement</h3>

  <!-- Search Box -->
  <div class="input-group mb-4 shadow-sm">
    <input type="text" class="form-control" id="search_payment_info" placeholder="Search by Plate Number or Owner">
    <button class="btn btn-primary" type="button" onclick="load_payment_report()">Search</button>
  </div>

  <!-- Action Buttons -->
  <!-- <div class="d-flex gap-2 mb-3">
    <button type="button" class="btn btn-primary btn-sm"><i class="bi bi-box-fill me-1"></i>Get Transaction</button>
    <button id="print_statement" class="btn btn-success btn-sm"><i class="bi bi-printer-fill me-1"></i>Print</button>
    <button id="export_statement" class="btn btn-info btn-sm"><i class="bi bi-file-earmark-arrow-up-fill me-1"></i>Export</button>
    <button id="pdf_statement" class="btn btn-danger btn-sm"><i class="bi bi-file-earmark-arrow-down-fill me-1"></i>PDF</button>
  </div> -->

  <!-- Statement Output (Hidden by default) -->
  <div id="statement_output" class="bg-white p-4 rounded shadow-sm" style="display: none;">
    <img src="../images/m_f.png" alt="Logo" width="200" class="mb-3">

    <!-- Vehicle Info Section -->
    <div class="row mb-4 border-bottom pb-3">
      <div class="col-md-6">
        <p><strong>Number Plate:</strong> <span id="plate_number"></span></p>
        <p><strong>Vehicle Type:</strong> <span id="vehicle_type"></span></p>
        <p><strong>Tax per Quarter:</strong> $<span id="tax_per_quarter"></span></p>
      </div>
      <div class="col-md-6">
        <p><strong>Owner Name:</strong> <span id="owner_name"></span></p>
        <p><strong>Owner Phone:</strong> <span id="owner_phone"></span></p>
        <p><strong>Last Payment Date:</strong> <span id="last_payment_date"></span></p>
      </div>
    </div>

    <!-- Payment Summary Table -->
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover">
        <thead class="table-primary">
          <tr>
            <th>Quarters Due</th>
            <th>Total Tax Due</th>
            <th>Total Paid</th>
            <th>Unpaid Balance</th>
          </tr>
        </thead>
        <tbody id="payment_summary">
          <!-- JavaScript will inject rows here -->
        </tbody>
      </table>
    </div>
  </div>
</div>


        <!-- [ Main Content ] end -->
    </div>
</div>
    </div>
</div>



<script src="../js/miniReport.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>





