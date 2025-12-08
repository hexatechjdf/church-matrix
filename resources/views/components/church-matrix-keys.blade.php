 @push('style')
     @include('components.card-general-styling')
 @endpush

 @php($admin = $admin ?? null)
 <div id="admin-section">
     <div class="row">
         <!-- Church Matrix API -->
         <div class="col-lg-6">
             <form action="{{ route('church-matrix.save-api') }}" method="POST" data-res="regions" class="form-submit">
                 @csrf
                 <div class="card-modern">
                     <div class="card-header-modern info">
                         <h5>Church Matrix API</h5>
                     </div>
                     <div class="card-body-modern">
                         <div class="form-group">
                             <label class="form-label">
                                 User Auth (Email) <span class="required">*</span>
                             </label>
                             <input type="email" name="church_matrix_user" class="form-control"
                                 value="{{ $settings->access_token ?? '' }}" required>
                         </div>

                         <div class="form-group">
                             <label class="form-label">
                                 Church Matrix API Key <span class="required">*</span>
                             </label>
                             <input type="text" name="church_matrix_api" class="form-control"
                                 value="{{ $settings->refresh_token ?? '' }}" required>
                             <div class="helper-text">
                                 <span>Your API key is encrypted and stored securely</span>
                             </div>
                         </div>
                     </div>
                     <div class="card-footer-modern">
                         <button type="submit" class="btn-modern btn-info">
                             <span>Save API Credentials</span>
                         </button>
                     </div>
                 </div>
             </form>
         </div>

         <!-- Region Selection -->
         <div class="col-lg-6">
             <div class="card-modern">
                 <div class="card-header-modern success">
                     <h5>Region Selection</h5>
                 </div>
                 <form action="{{ route('church-matrix.save-region') }}" method="POST" class="form-submit">
                     @csrf
                     <div class="card-body-modern">
                         <div class="alert-modern">
                             <div class="content">
                                 <strong>Important</strong>
                                 Please save your Church Matrix API credentials first before selecting a region.
                             </div>
                         </div>


                         <div class="form-group">
                             <label class="form-label">
                                 Select Region <span class="required">*</span>
                             </label>
                             <select class="form-control" name="region_id" id="region_id"
                                 {{ $regions ? '' : 'disabled' }}>
                                 <option value="">-- Choose Region --</option>
                                 @if ($regions)
                                     @foreach ($regions as $region)
                                         <option value="{{ $region['id'] }}"
                                             {{ @$settings->company_id == $region['id'] ? 'selected' : '' }}>
                                             {{ $region['name'] }}
                                         </option>
                                     @endforeach
                                 @endif
                             </select>
                         </div>
                     </div>

                     <div class="card-footer-modern">
                         <button type="submit" class="btn-modern btn-success">
                             <span>Save Region</span>
                         </button>
                     </div>
                 </form>
             </div>
         </div>

         @if ($admin)
             <div class="col-lg-12">
                 <div class="card-modern">
                     <div class="card-header-modern warning  bg-warning">
                         <h5>Subaccount</h5>
                     </div>
                     <form action="{{ route('church-matrix.save-location') }}" method="POST" class="form-submit">
                         @csrf
                         <div class="card-body-modern">

                             <div class="form-group">
                                 <label for="location_id">Location ID <span class="text-danger">*</span></label>
                                 <input type="text" class="form-control @error('location_id') is-invalid @enderror"
                                     name="location_id" id="location_id" placeholder="Enter Location ID"
                                     value="{{ $settings->location_id ?? '' }}">
                                 @error('location_id')
                                     <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                                 @enderror
                             </div>
                         </div>

                         <div class="card-footer-modern">
                             <button type="submit" class="btn-modern btn-warning">
                                 <span>Save</span>
                             </button>
                         </div>
                     </form>
                 </div>
             </div>
         @endif
     </div>
 </div>
