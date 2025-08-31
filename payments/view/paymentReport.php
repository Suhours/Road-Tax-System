

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
</style>



<div class="col-lg-10 py-3">
        
<div class="container" style="margin-top: 100px;">
        

<h2 style="font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;">payment Report area</h2>


<div class="pc-container bg-white p-4 rounded shadow-sm">

     



      <div class="pc-content">
        <!-- [ breadcrumb ] start -->



    

  <div class="row mb-3">
  <div class="col-sm-12 mb-2 d-flex justify-content-start gap-2">
    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-box-fill" style="margin-right: 10px;"></i>Get Transaction</button>
    <button id="print_statement" class="btn btn-success btn-sm btn-print-none"><i class="bi bi-printer-fill" style="margin-right: 10px;"></i>Print</button>
    <button id="export_statement" class="btn btn-info btn-sm btn-print-none"><i class="bi bi-file-earmark-arrow-up-fill" style="margin-right: 10px;"></i>Export</button>
    <button id="pdf_statement" class="btn btn-danger btn-sm btn-print-none"><i class="bi bi-file-earmark-arrow-down-fill" style="margin-right: 10px;"></i>PDF</button>
  </div>

  <!-- Form Inputs -->



            <div class="search-box">
              <div class="input-group mb-3">
                  <input type="text" class="form-control" id="search_payment_info" placeholder="Search by Plate Number or Owner">
                 <button class="btn btn-primary" type="button" onclick="load_payment_info()">Search</button>
              </div>
            </div>


</div>



<div class="table-responsive"  id="print_area" style="max-height: 300px;overflow: auto;">

<img width="225px" style="margin-bottom: 10px;" height="80px" src="../images/m_f.png">
      
    <table class="table  table-sm"   id="payment_report" >
        <thead class="table-primary" style="font-weight: 700;">
            <tr>
           
            <th>NumberPlate</th>
            <th>OwnerName</th>
            <th>OwnerPhone</th>
            <th>VehicleType</th>
            <th>AmountPerThreeMonth</th>
            <th>LastPaymentDate</th>
            <th>QuartersDue</th>
            <th>TotalTaxDue</th>
            <th>TotalPaid</th>
            <th>UnpaidBalance</th>
            </tr>
          
        
            
        </thead>

        <tbody>
          <tr>

          </tr>

        

        </tbody>
    </table>

</div>








        <!-- [ Main Content ] end -->
    </div>
</div>





        <div class="copyright">
            © 2025 All Rights reserved: MOF.SSC-KHAATUMO
        </div>
    </div>
</div>



<script src="../js/paymentReport.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>





