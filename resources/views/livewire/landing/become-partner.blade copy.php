

@section('homeActive','active')

<!--begin::Landing hero-->
<div class="d-flex flex-column bg-base-gradient flex-center w-100 min-h-300px min-h-lg-500px px-9">
	<style>
		.toggle-btn{
			background: white;
			border-radius: 50px;
			display: flex
		}
		
		.toggle-active{
			background: #333 !important;
			color: white !important;;
			padding: 15px !important;
			border-radius: 50px;
			width: 50%;
		}
		.toggle-Inactive{
			width: 50%;
		}
	</style>
		
    <!--begin::Heading-->
	<div class="container">

		<div class="row mb-lg-10">
		
			<div class="col-md-6 text-left"  >
				<form wire:submit.prevent="store()" class="bg-light  mb-5 mb-lg-10 p-5 p-lg-10 rounded partner-form" method="post">
					<!--begin::Title-->
					<h1 class="text-base lh-base fw-bold fs-2x fs-lg-3x mb-5 " data-aos="fade-up"  >{!!__('Become a Partner')!!}</h1>  
					<div class="toggle-btn mb-5">
						<button type="button" wire:click="setCompany()" class="btn {{($is_company==1)?'toggle-active':' toggle-Inactive'}}">Company</button>
						<button type="button" wire:click="setCompany()" class="btn {{($is_company==0)?'toggle-active':' toggle-Inactive'}}">Individual</button>
					</div>

					<div class="row">
						<div class="col-md-6 my-3">
							<input type="text" wire:model="name"  class="form-control partner-field" placeholder="Company Name">
							@error('name') <span class="text-danger">{{$message}}</span> @enderror
						</div>
						<div class="col-md-6 my-3">
							<input type="email" wire:model="email"  class="form-control partner-field" placeholder="Email">
							@error('email') <span class="text-danger">{{$message}}</span> @enderror

						</div>
						<div class="col-md-12 my-3">
							<input type="text" wire:model="phone"  class="form-control partner-field" placeholder="Phone">
						</div>
						<div class="col-md-6 my-3" wire:ignore>
							<select class="form-select partner-field"  id="country" data-placeholder="Select Country">
								<option value="">Select Country</option>
								@foreach ($countries as $item)
									<option value="{{$item['name']['common']}}" {{(($item['name']['common']==$country))?'selected':''}} data-image="{{$item['flags']['png']}}"
									>{{$item['name']['common']}}</option>
								@endforeach 
							</select>
							@error('country') <span class="text-danger">{{$message}}</span> @enderror
						</div>
						<div class="col-md-6 my-3" wire:ignore>
							<select  class="form-select partner-field" id="nationality" data-placeholder="Select Nationality">
								<option value="">Select Nationality</option>
									@foreach ($countries as $item)
										    <option value="{{$item['name']['common']}}" {{(($item['name']['common']==$nationality))?'selected':''}} data-image="{{$item['flags']['png']}}"
                                            >{{$item['name']['common']}}</option>
                                    @endforeach   
							</select>
							@error('nationality') <span class="text-danger">{{$message}}</span> @enderror
							
						</div>
						<div class="col-md-6 my-3" wire:ignore>
							<select  class="form-select partner-field" multiple data-control="select2" data-placeholder="Select Language"  id="languages">
								<option value="">Select Languages</option>
								@foreach ($languages as $item)
									<option value="{{$item['name']}}" {{(in_array($item['name'],$vendor_languages))?'selected':''}}>{{$item['name']}}</option>
								@endforeach 
							</select>
							@error('languages') <span class="text-danger">{{$message}}</span> @enderror
						</div>
						<div class="col-md-6 my-3" wire:ignore>
							<select wire:model="vendor_arbitrators"  class="form-select partner-field" multiple data-control="select2" data-search="true" data-placeholder="Select Arbitrators">
								<option value="">Select Arbitrators</option>
								@foreach ($arbitrators as $item)
									<option value="{{$item->id}}" {{(in_array($item->id,$vendor_arbitrators))?'selected':''}}>{{$item->name}}</option>
								@endforeach                  
							</select>
							@error('vendor_arbitrators') <span class="text-danger">{{$message}}</span> @enderror
						</div>
						<div class="col-md-6 my-3" wire:ignore>
							<select wire:model="vendor_categories"  class="form-select partner-field" data-control="select2" data-search="true" data-placeholder="Select Categories">
								<option value="">Select Categories</option>
								@foreach ($categories as $item)
										    <option value="{{$item->id}}" {{(in_array($item->id,$vendor_categories))?'selected':''}}>{{$item->name}}</option>
                                @endforeach                   
							</select>
							@error('vendor_categories') <span class="text-danger">{{$message}}</span> @enderror
						</div>
						<div class="col-md-6 my-3">
							<select wire:model="vendor_sub_categories"  class="form-select partner-field" data-control="select2" id="">
								<option value="">Sub Categories</option>
							</select>
							@error('vendor_sub_categories') <span class="text-danger">{{$message}}</span> @enderror
						</div>
						<div class="col-md-12 my-3">
							<textarea  wire:model="about"  class="form-control partner-field" placeholder="About Yourself" rows="4"></textarea>
							@error('about') <span class="text-danger">{{$message}}</span> @enderror

						</div>
						<div class="col-md-6 my-3">
							<input type="number" step="0.01" wire:model="min_case_value" class="form-control partner-field" placeholder="Minimum Case Value">
							@error('min_case_value') <span class="text-danger">{{$message}}</span> @enderror

						</div>
						<div class="col-md-6 my-3">
							<input type="number" wire:model="cases_won"  class="form-control partner-field" placeholder="Cases Won">
							@error('cases_won') <span class="text-danger">{{$message}}</span> @enderror
						</div>
						

						<div class="col-md-12 my-3">
							<div class="form-check ">
								<input class="form-check-input border-base" type="checkbox" value="" required="" id="privacy_policy">
								<label class="form-check-label fs-6" for="privacy_policy">
									Accept all Privacy policy & Terms & conditions
								</label>
							</div>
							
						</div>

						<div class="col-md-12 my-3">
							 <button type="submit" class="btn btn-base-black w-100 rounded-pill py-5">Create Account</button>
						</div>
					</div>
				</form>
			</div>
			<div class="col-md-6 text-right" data-aos="fade-left" > 
				@php
					$img_ar=(app()->getLocale()=="ar")?'-ar':'';
				@endphp
				
				<img src="{{ asset('media/partner.png') }}" style="{{(app()->getLocale()=='ar')?'left:-6.5vw':'right: -6.5vw'}};" 
				 class="w-100 d-md-block d-lg-block d-none image{{$img_ar}} partner-img"  alt="building" > 
					
			</div>
		</div>
	</div>
    
    <!--end::Heading-->
</div>


@section('scripts')

<script>
	 $('#country').select2({
			templateResult: formatCountry,
		}).on('change', function (e) {
			@this.set('country', $(this).val());
		});
		$('#nationality').select2({
			templateResult: formatCountry
		}).on('change', function (e) {
			@this.set('nationality', $(this).val());
		});

		function formatCountry (country) {
			if (!country.id) { return country.text; }
			var $country = $(
				'<span><img src="' + $(country.element).data('image') + '" class="img-flag" width="20px" /> ' + country.text + '</span>'
			);
			return $country;
		};
</script>
	
@endsection