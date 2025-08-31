
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

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
        
<div class="container bg-white p-4 rounded shadow-sm" style="margin-top: 100px;">
        <div class="header">
            <h4 style="margin-bottom: 20px;color:#007bff;font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;">Payment Center</h4>
            <div class="row">
            <div class="col-sm-6">

            <form id="search_vehicle">
            <div class="search-box">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="search_vihicle_info" placeholder="Search by Plate Number or Owner">
                    <button class="btn btn-primary" type="button">Search</button>
                </div>
            </div>
            </form>
            </div>
            <div class="col-sm-6 text-end">
            <button class="btn btn-outline-primary me-2 " id="add_payment"><i class="fas fa-plus me-1"></i>Add Payment</button>
            </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover" id="payment_table">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Number_plate</th>
                        <th>Amount</th>
                        <th>Reciept_num</th>
                        <th>Date</th>
                        <!-- <th>Actions</th> -->
                    </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        </div>


<div class="modal" id="payment_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background-color: #007bff !important; color:rgb(255, 255, 255) !important;">
        <h1 class="modal-title fs-5" id="staticBackdropLabel">Payments</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

    

      </div>
      <div class="modal-body">
      <form id="payment_form" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" id="id">


        
        
        <div class="row">

        <div class="alert alert-success  d-none" role="alert">
        A simple success alert—check it out!
        </div>
        <div class="alert alert-danger   d-none" role="alert">
        A simple danger alert—check it out!
        </div>


            <div class="col-12 mb-3">
                <label class="form-label">Plate Number</label>
                <input type="text" class="form-control" id="number_plate_input" placeholder="Enter plate number" required>
                <div class="invalid-feedback">Please enter plate number</div>
            </div>
            
            
            <div class="col-12 mb-3">
                <label class="form-label">Unpaid Balance</label>
                <input type="number" class="form-control" id="unpaid_balance" disabled>
            </div>
            
            <div class="col-12 mb-3">
                <label class="form-label">Amount paid</label>
                <input type="number" class="form-control" id="amount_paid" disabled>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">Description</label>
                <input type="text" class="form-control" id="description">
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Record Payment</button>
        </div>
    </form>
</div>
  </div>
</div>




        <div class="copyright">
            © 2025 All Rights reserved: MOF.SSC-KHAATUMO
        </div>
    </div>
</div>



<script src="../js/payments.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>