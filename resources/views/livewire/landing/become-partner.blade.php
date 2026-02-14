

<div >
	@section('homeActive','active')

	@php
		$img_ar=(app()->getLocale()=="ar")?'-ar':'';
	@endphp

	<div class="container">
		
		<div class="row mb-lg-10">
			<div class="col-md-6 text-left"  >
				@if ($status==404)
					<div class="bg-light  mb-5 mb-lg-10 p-5 p-lg-10 rounded partner-form text-center" style="min-height: 700px">
						<img src="{{asset('media/404.png')}}" alt="404" class="w-50 mt-5 mb-3">
						<h1 class="display-6">Ah! Sorry, your invite is Expired <br> or removed from company</h1>
					</div>
				@elseif ($status==409)
					<div class="bg-light  mb-5 mb-lg-10 p-5 p-lg-10 rounded partner-form text-center" style="min-height: 700px">
						<img src="{{asset('media/already.png')}}" alt="404" class="w-50 mt-5 mb-3">
						<h1 class="display-6">You have already account with this <br> Email address!</h1>
						<a href="{{url('partner/login')}}" class="btn btn-base">Login Now</a>
					</div>
				@else
					<form wire:submit.prevent="store()" enctype="multipart/form-data" class="bg-light  mb-5 mb-lg-10 p-5 p-lg-10 rounded partner-form" method="post">
						<!--begin::Title-->
						@if ($invite)
							<h1 class="text-base lh-base fw-bold fs-1x fs-lg-2x mb-5 " data-aos="fade-up" wire:ignore  >You're Invited from {{$invite['company']['name'] ?? 'Company'}}. <br> Join Now</h1>  
						@else
							<h1 class="text-base lh-base fw-bold fs-2x fs-lg-3x mb-5 " data-aos="fade-up" wire:ignore  >{!!__('Become a Partner')!!}</h1>  

							<div class="toggle-btn mb-5">
								<button type="button" wire:click="setCompany()" class="btn {{($is_company==1)?'toggle-active':' toggle-Inactive'}}">{{__('Company')}}</button>
								<button type="button" wire:click="setCompany()" class="btn {{($is_company==0)?'toggle-active':' toggle-Inactive'}}">{{__('Individual')}}</button>
							</div>

						@endif

						

						<div class="row">
							<div class="col-md-6 my-3">
								<input type="text" wire:model="name"  class="form-control partner-field" placeholder="{{($is_company)?__('Company Name'):__('Name')}}">
								@error('name') <span class="text-danger">{{$message}}</span> @enderror
							</div>
							<div class="col-md-6 my-3">
								<input type="text" wire:model="email" {{($invite!=null)?'readonly':''}}  class="form-control partner-field" placeholder="{{__('Email')}}">
								@error('email') <span class="text-danger">{{$message}}</span> @enderror

							</div>
							<div class="col-md-12 my-3">
								<div class="input-group  mb-3 phone-field">
									<div class="input-group-prepend" wire:ignore>
									<span class="input-group-text p-0 border-0" id="basic-addon1">
											<div id="phoneCode" wire:ignore></div>
										</span>
									</div>
									<input type="number" wire:model="phone"  class="form-control border-0" placeholder="{{__('Phone')}}">
								</div>
								@error('phone') <span class="text-danger">{{$message}}</span> @enderror
							</div>
							<div class="col-md-6 my-3">
								<div id="country" wire:ignore></div>
								@error('country') <span class="text-danger">{{$message}}</span> @enderror
							</div>
							<div class="col-md-6 my-3">
								<input type="text" wire:model="city"  class="form-control partner-field" placeholder="{{__('City')}}">
								@error('city') <span class="text-danger">{{$message}}</span> @enderror
							</div>
							<div class="col-md-6 my-3" >
								<div id="nationality" wire:ignore></div>
								@error('nationality') <span class="text-danger">{{$message}}</span> @enderror
							</div>
							<div class="col-md-6 my-3">
								<div id="languages" wire:ignore></div>
								@error('vendor_languages') <span class="text-danger">{{$message}}</span> @enderror
							</div>
							<div class="col-md-6 my-3">
								<div id="arbitrators" wire:ignore></div>
								@error('vendor_arbitrators') <span class="text-danger">{{$message}}</span> @enderror
							</div>
							<div class="col-md-6 my-3" >
								<div id="jurisdictions" wire:ignore></div>
								@error('vendor_jurisdictions') <span class="text-danger">{{$message}}</span> @enderror
							</div>

							<div class="col-md-12 my-3" >
								<div id="categories" wire:ignore></div>
								@error('vendor_categories') <span class="text-danger">{{$message}}</span> @enderror
							</div>
							<div class="col-md-12 my-3">
								<div id="sub_categories" wire:ignore></div>
								@error('vendor_sub_categories') <span class="text-danger">{{$message}}</span> @enderror
							</div>
							<div class="col-md-6 my-3">
								<input type="number" wire:model="cases_won"  class="form-control partner-field" placeholder="{{__('Cases Won')}}">
								@error('cases_won') <span class="text-danger">{{$message}}</span> @enderror
							</div>
							
							<div class="col-md-12 my-3">
								<textarea  wire:model="about"  class="form-control partner-field" placeholder="{{__('About Yourself')}}" rows="4"></textarea>
								@error('about') <span class="text-danger">{{$message}}</span> @enderror

							</div>
						
							
							@if($is_company==0)
								<div class="col-md-6 fv-row fv-plugins-icon-container my-3">
									<label class="required fs-6 fw-semibold mb-2">{{__('Certificate')}}</label>
									<div class="mt-1"> 
										<div class="image-input image-input-outline image-input-placeholder image-input-empty image-input-empty" data-kt-image-input="true">
											<div class="image-input-wrapper w-100px h-100px p-2" style="background-image: url('{{($certificate!=null)?'':asset('storage/uploads/file.png')}} ')">
												{{($certificate!=null)?wordwrap($certificate->getClientOriginalName(), 13, "\n", true):''}}</div>
											<label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="Change avatar" data-kt-initialized="1">
												<i class="bi bi-upload fs-7"></i>
												<input type="file" wire:model="certificate" name="certificate" >
											</label>
										</div>
									</div>
									@error('certificate') <span class="text-danger">{{$message}}</span> @enderror
								</div>
							@endif

							<div class="col-md-6 fv-row fv-plugins-icon-container my-3">
								<label class="required fs-6 fw-semibold mb-2">{{__(($is_company==1)?'certificate_lisence':'License')}}</label>
								{{-- <input type="file" wire:model="license" class="form-control " > --}}
								<div class="mt-1"> 
									<div class="image-input image-input-outline image-input-placeholder image-input-empty image-input-empty" data-kt-image-input="true">
										<div class="image-input-wrapper w-100px h-100px p-2" style="background-image: url('{{($license!=null)?'':asset('storage/uploads/file.png')}} ')">
											{{($license!=null)?wordwrap($license->getClientOriginalName(), 13, "\n", true):''}}</div>
										<label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="Change avatar" data-kt-initialized="1">
											<i class="bi bi-upload fs-7"></i>
											<input type="file" wire:model="license" name="license" >
										</label>
										{{-- <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow {{($certificate==null)?'d-none':''}}"
											data-toggle="tooltip" title="Remove file"  >
											<i class="bi bi-x fs-2"></i>
										</span> --}}
									</div>
								</div>
								@error('license') <span class="text-danger">{{$message}}</span> @enderror
							</div>

							{{-- Picture --}}
							<div class="col-md-6 fv-row fv-plugins-icon-container my-3">
								<!--begin::Label-->
								<label class="fs-6 fw-semibold mb-3">
									<span>{{__('Upload Picture')}}</span>
									<i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" aria-label="Allowed file types: png, jpg, jpeg." data-kt-initialized="1"></i>
								</label>
								<div class="mt-1"> 
									<div class="image-input image-input-outline image-input-placeholder image-input-empty image-input-empty" data-kt-image-input="true">
									
										<div class="image-input-wrapper w-100px h-100px" style="background-image: url('{{($image!=null)?$image->temporaryUrl():asset('uploads/blank2.jpg')}} ')"></div>
									
										<label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="Change avatar" data-kt-initialized="1">
											<i class="bi bi-pencil-fill fs-7"></i>
											<input type="file" wire:model="image" name="image" accept=".png, .jpg, .jpeg">
										</label>
										<span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" aria-label="Cancel avatar" data-kt-initialized="1">
											<i class="bi bi-x fs-2"></i>
										</span>
										<span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" aria-label="Remove avatar" data-kt-initialized="1">
											<i class="bi bi-x fs-2"></i>
										</span>
									</div>
								</div>
								@error('image') <span class="text-danger">{{$message}}</span> @enderror

							</div>
							
							<div class="col-md-12 my-3 ">
								<div class="form-check ">
									<input class="form-check-input {{(app()->getLocale()=="ar")?'float-end ms-3':''}} border-base" type="checkbox" value="1" wire:model="privacy_policy"  id="privacy_policy">
									<label class="form-check-label fs-6" for="privacy_policy">
										{!!__('accept_privacy')!!}
									</label>
								</div>
								@error('privacy_policy') <span class="text-danger">{{$message}}</span> @enderror
							</div>

							<div class="col-md-12 my-3">
								<button type="submit" class="btn btn-base-black w-100 rounded-pill py-5" wire:loading.attr="disabled">
									<span class="indicator-label"  wire:loading.remove >{{__('Create Account')}}</span>
										<span class="indicator-progress"  wire:loading >{{__('Please wait')}}...
										<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
								</button>
							</div>


						</div>
					</form>
				@endif
			</div>
			<div class="col-md-6 text-right " data-aos="fade-left" wire:ignore> 
				
				<img src="{{ asset('media/partner.png') }}"  style="{{(app()->getLocale()=='ar')?'left:-6.5vw':'right: -6.5vw'}};" 
				 class="w-100 d-md-block d-lg-block d-none image{{$img_ar}} partner-img{{$img_ar}} "  alt="building" > 
					
			</div>
		</div>
	</div>

		
	@section('scripts')
		@include('components.vendor-script')

	@endsection	

	
</div>