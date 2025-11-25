<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" integrity="sha512-vKMx8UnXk60zUwyUnUPM3HbQo8QfmNx7+ltw8Pm5zLusl1XIfwcxo8DbWCqMGKaWeNxWA8yrx5v3SaVpMvR3CA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title>Planning Center</title>
    <style>
        #loadingg {
            width: 100%;
            height: 100%;
            top: 0px;
            left: 0px;
            position: fixed;
            display: block;
            z-index: 99
        }

        #loading-image {
            position: absolute;
            left: 50%;
            top: 50%;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
            z-index: 99999999999999;

        }

        .loading-overlay {
            background: rgb(0, 0, 0);
            opacity: 0.5;
            filter: alpha(opacity=50);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 999;

        }
        
        
       .tdd {
            position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        }
        

.disb{
    background-color:grey;
    
}
    
    </style>
  </head>
  <body>
      
      <div id="loadingg">
        <img id="loading-image" src="{{ asset('load.svg') }}" alt="Loading..." />
    </div>

    <div id="remove-overlay" class="loading-overlay"></div>
    

<div class="p-4 bg-white">
    <div class="row">
        
        <div class="col-md-4 py-2">
            <img src="https://storage.googleapis.com/msgsndr/NP4dT88lEnnjb3WVmyAQ/media/640b02ecd29c8caf3f6233b4.png" style="height:100px; width:100%; object-fit: contain;">
            <a data-href="#"
                        class="btn  form-control connect text-white   mt-0 mt-1 planningcenterbtn" style='background-color:#E75037'><i
                            class="mdi mdi-plus-circle-outline mr-2"></i>Connect</a>
                              <div class="planning_center_data d-none">
                 <div class="form-group">
                     <label>Choose Planning Center Workflow </label>
                     <select class="form-control select2 workflowsselected" onchange="return workflowchanged(this.value)">
                         
                         
                     </select>
                     <span>(Automatically add new Church Funnels contacts to this Planning Center workflow)</span>
                 </div>
             </div>     
                             <a data-href="#"
                        class="btn form-control planningcenterbtn text-white  disconnect d-none mt-0 mt-1" onclick="return disconnectplanning(this)" style='background-color:#E75037'>Disconnect - <span id="organization_id"></span></a>
                        
                        
                        
                        <a 
                        class="btn form-control text-white mt-3 mt-1" href="https://churchfunnels.com/planning-center-integration" target="_blank"  style='background-color:#1c5be1'>How to Connect - Help Docs</a>
                        
                        
                     
        </div>
        
       
    </div>
   
    </div>
      
      
    
   <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js" integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
        var parentWindow = window.parent;
        window.addEventListener("message", (e) => {
            var data = e.data;
           console.log(data);
            // if (typeof data == 'string' && data == 'authconnected') {
            //     childframe.close();
            //     window.location.href = "/";
            // }
            
            if (data.type == 'location') {
                
                
            }
        });
      
        $(document).ready(function(){
   $('#loadingg').show();
            $('#remove-overlay').show();
            //parentWindow.postMessage('authconnecting', '*');
            
            @if(request()->has('locationid') && request()->has('sessionKey'))
                
                checkForauth({
                    location:'{{request()->locationid}}',
                    token:'{{request()->sessionKey}}'
                });
                
            @endif
});
        var childframe = null;

        function connect(url) {
            childframe = window.open(url, 'childframe', 'width=500,height=500');
            childframe.addEventListener("message", (e) => {
                var data = e.data;
                if (typeof data == 'string' && data == 'planningconnected') {
                     toastr.success("Planning Center Connected successfully!");
                    childframe.close();
                   hideshowplanning();
                   getWorkflows();
                    // window.location.href = "/";
                }
            });
        }
        
        function getWorkflows(){
              var url = "{{ route('auth.listworkflows') }}";
            $.ajax({
                url: url,
                type: 'GET',
                data: {
                     
                      token: userjwt
                },
                success: function(data) {
                  
                   
                   hideshowplanning(data);
                
                },
                error: function(data) {
                    console.log(data);
                },
                complete: function() {
                     $('#loadingg').hide();
                    $('#remove-overlay').hide();
                }
            });
        }
        
        $('body').on('click', '.planningcenterbtn.connect', function(e){
            e.preventDefault();
            if($(this).hasClass('disb')){
                return false;
            }
            connect($(this).attr('data-href'));
        });
        
        let mainusertoken='';
        let userjwt='';
        function workflowchanged(value){
            var url = "{{ route('auth.saveWorkflow') }}";
            $.ajax({
                url: url,
                type: 'GET',
                data: {
                     workflow_id: value,
                      token: userjwt
                },
                success: function(data) {
                  
                   
                    console.log(data);
                
                },
                error: function(data) {
                    console.log(data);
                },
                complete: function() {
                     $('#loadingg').hide();
                    $('#remove-overlay').hide();
                }
            });
        }
        
        
         function disconnectplanning(e){
            
            var url = "{{ route('auth.disconnectplanning') }}";
            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    
                      token: userjwt
                },
                success: function(data) {
                  
                     $('.planningcenterbtn.connect').removeClass('disb');
                         $('.planningcenterbtn.connect').show();
                          $('.planningcenterbtn.disconnect').addClass('d-none');
                    console.log(data);
                     $('.planning_center_data').addClass('d-none');
                
                },
                error: function(data) {
                    console.log(data);
                },
                complete: function() {
                     $('#loadingg').hide();
                    $('#remove-overlay').hide();
                }
            });
        }
        function hideshowplanning(data){
            
            $('.planningcenterbtn.connect').addClass('disb');
                         $('.planningcenterbtn.connect').hide();
                         if(data?.organization_id){
                             $('#organization_id').html(data.organization_id??'');
                             
                             if(data?.organization_name){
                                 
                                 $('#organization_id').html(data.organization_name??'');
                             }
                             
                             
                         }
                          
                          $('.planningcenterbtn.disconnect').removeClass('d-none');
             if(data?.workflows?.data){
                       $('.planning_center_data').removeClass('d-none');
                       $('.workflowsselected').html('<option value="">Select Workflow</option>');
                       if(data.workflows.data.length==0){
                          //  $('.planning_center_data').addClass('d-none');
                       }
                        data.workflows.data.forEach(item=>{
                            var selected='';
                            if(data?.workflow_selected && item.id==data.workflow_selected){
                                    selected = 'selected="selected"';
                            }
                            $('.workflowsselected').append(`<option value="${item.id}" ${selected}>${item.attributes.name}</option>`);
                        });
                   }              
        }
        
        // let token = localStorage.getItem('token') || "";
        // console.log(token);

        function checkForauth(dt) {
            
            var url = "{{ route('auth.checking') }}";
            $.ajax({
                url: url,
                type: 'GET',
                data: {
                     location: dt.location,
                      token: dt.token
                },
                success: function(data) {
                    toastr.success("Location fetched successfully!");
                   mainusertoken = data.token;
                   userjwt = data.jwt;
                //   localStorage.setItem('token',data.token);
                    $('.planningcenterbtn.connect').attr('data-href',data.planning_href);
                    if(data.is_planning){
                        
                        hideshowplanning(data);
                       getWorkflows();
                            
                        
                    }
                   
                  
                    
                
                },
                error: function(data) {
                    console.log(data);
                },
                complete: function() {
                     $('#loadingg').hide();
                    $('#remove-overlay').hide();
                }
            });
        }
    </script>
  </body>
</html>

