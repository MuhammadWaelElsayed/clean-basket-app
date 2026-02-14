

@section('homeActive','active')
<div>
<style>
	.vscomp-toggle-button {
    background-color: #f5f8fa;
}
</style>
	<!--begin::Landing hero-->
<div class="d-flex flex-column bg-base-gradient flex-center w-100 min-h-300px min-h-lg-500px px-9">
    <!--begin::Heading-->
	<div class="container">
		<div class="row ">
		
			<div class="col-md-5 text-left"  >
				<div class=" mb-5 mb-lg-10 py-10" >
					<!--begin::Title-->
                    <p class="lead mt-15">{{__('home_top_title')}}</p>
					<h1 class="text-base lh-base fw-bold fs-2x fs-lg-3x mb-5 " data-aos="fade-up"  >{!!__('Home_Title')!!}</h1>  
					<p class="fs-4 mt-10 text-gray-600" data-aos="fade-up">{!!__('home_desc')!!}</p>
					<br>
				   
					<!--end::Action-->
				</div>
			</div>
			<div class="col-md-7 text-right" data-aos="fade-left" > 
				@php
					$img_ar=(app()->getLocale()=="ar")?'-ar':'';
				@endphp
				
				<img src="{{ asset('media/building.png') }}" style="position:absolute; {{(app()->getLocale()=="ar")?'left:-6.5vw':'right: -6.5vw'}};"  class="w-100 d-md-block d-lg-block d-none image{{$img_ar}}"  alt="building" > 
					
			</div>
		</div>
	</div>
</div>

<section class="about min-h-200px" id="about">
	<div class="container  pt-lg-20" data-aos="fade-up">
		<div class="row">
			<div class="col-md-5 px-20" data-aos="fade-right" >
				<img src="{{ asset('media/about-mobiles.png') }}"  class="w-100 image{{$img_ar}}" alt="vision" > 

			</div>
			<div class="col-md-7 ">
				<p class="lead mt-15">{!!__('about_top_title')!!}</p>
				<h1 class="text-base lh-base fw-bold fs-2x fs-lg-3x mb-5 " data-aos="fade-up"  >{!!__('about_title')!!}</h1>  
				<p class="fs-4 mt-10 text-gray-600" data-aos="fade-up">{!!__('about_desc')!!}</p>
				{{-- <p class=" text-gray-600" data-aos="fade-up">{!!__('about_points')!!}</p> --}}
			</div>
		</div>
		
	</div>
</section>


<section class="download mt-20 bg-base-black p-lg-20 p-0" id="download">
	<div class="container" data-aos="flip-left">
		<div class="row">
			<div class="col-md-7 p-15 text-white d-flex align-items-center ">
				<div>
					<h1 class="lh-base fw-semibold fs-3x fs-lg-1x mb-5 text-white"  >{!!__('download_title')!!}</h1>  
					<p class="fs-4 text-gray-400 pe-5" >{!!__('download_desc')!!}</p> <br>
					<div class="d-lg-flex mt-5">
						<a href="" class="download-btn">	<img src="{{ asset('media/google'.$img_ar.'.png') }}"  class=" w-100px me-5 mb-5" alt="download" > </a>
						<a href="" class="download-btn"><img src="{{ asset('media/apple'.$img_ar.'.png') }}"  class="w-100px " alt="download" > </a>
					</div>
				</div>
			</div>
			
			<div class="col-md-5 p-20">
				<img src="{{ asset('media/download_mobiles.png') }}"  class="w-100 image{{$img_ar}}" alt="message" > 

			</div>
		</div>
		

	</div>
</section>

<section class="contact mt-20  px-lg-20 px-0" id="contact" wire:ignore>
	<div class="container"  data-aos="flip-right">
		<div class="row">
			<div class="col-md-7 p-15 " >
				<h1 class="display-6 lh-base fw-bold ps-5"  >{!!__('contact_title')!!}</h1>  
				<p class="fs-3 text-gray-600 mb-5  ps-5">{{__('contact_desc')}}</p> <br>
				<form wire:submit.prevent="submitContact()" method="post" >
					<div class="row  mx-auto  mb-10 mb-lg-20">
						<div class="col-lg-12 "> 
							<input type="text" wire:model="name" placeholder="{{__('Full Name')}}*"   class="form-control form-control-solid custom-input mb-10" >
							@error('name') <span class="text-danger">{{$message}}</span> @enderror

						</div>
					
						<div class="col-lg-6 ">
							<input type="text" wire:model="email" placeholder="{{__('Email')}}*"   class="form-control form-control-solid custom-input mb-10" >	
							@error('email') <span class="text-danger">{{$message}}</span> @enderror

						</div>
						<div class="col-lg-6 ">
							<div class="input-group  mb-3 phone-field bg-light mb-10">

								<div class="input-group-prepend" wire:ignore>
								  <span class="input-group-text p-0 border-0 " id="basic-addon1">
										<div id="phoneCode" class="" wire:ignore></div>
									</span>
								</div>
								<input type="number" wire:model="phone"  class="form-control border-0 bg-light " placeholder="{{__('Phone')}}">
							  </div>
							{{-- <input type="text" wire:model="phone" placeholder="{{__('Phone')}}"   class="form-control form-control-solid custom-input mb-10" >	 --}}
						</div>
						<div class="col-lg-12 ">
							<textarea wire:model="message" class="form-control form-control-solid custom-input mb-10" placeholder="{{__('Your Message')}}"    rows="3"></textarea>
						</div>
						<div class="col-lg-6 ">
							<button type="submit" id="" class="btn px-20 fs-lg-4 btn-dark rounded " >	{{__('Submit')}} </button>
						</div>
					</div>
				</form>
			</div>
			
			<div class="col-md-5 p-20">
				<h1 class="fs-2x lh-base fw-bold "  >{!!__('contact_info')!!}</h1>  
				{{-- <p class="fs-5 text-gray-600 my-5 ">{{__('info_desc')}}</p>  <br> --}}

				<p class="fs-5 text-gray-600  my-5 {{($img_ar=='-ar')?'text-end':''}}" style="direction: ltr" >{!!__('call_phone')!!}</p> 
				{{-- <p class="fs-5 text-gray-600  my-5 ">{!!__('open_timings')!!}</p>  --}}

				<h1 class="fs-2x lh-base fw-bold mt-15"  >{!!__('follow_us')!!}</h1>  
				<p class="mt-3">
					{{-- <a href="" class="text-gray-700 fw-bold fs-4 me-10">{!!__('Facebook')!!}</a>
					<a href="" class="text-gray-700 fw-bold fs-4 me-10">{!!__('Instagram')!!}</a> --}}
					<a href="https://www.linkedin.com/in/legal-platform-a233192b9" target="_blank" class="fw-bold me-10">
						<i class="fab fa-linkedin text-dark" style="font-size:28px"></i>
					</a>
					<a href="https://www.instagram.com/legalplatform.co/" target="_blank" class=" fw-bold me-10">
						<i class="fab fa-instagram text-dark" style="font-size:28px"></i>
					</a>
				</p>

			</div>
		</div>

	</div>
</section>

<script>

const phoneCodes = [
        @foreach ($countries as $codes)
            @foreach ($codes['idd']['suffixes'] as $suffix)
                {
                    label: "{{ $codes['idd']['root'].$suffix }}",
                    value: "{{ $codes['idd']['root'].$suffix  }}"
                },
            @endforeach
        @endforeach
    ];
    VirtualSelect.init({
        ele: '#phoneCode',
        options: phoneCodes,
        placeholder:'{{__("Phone Code")}}',
        additionalClasses:'phone-code-field',
        noOptionsText:'{{__("No options found")}}',
        noSearchResultsText:'{{__("No options found")}}',
        searchPlaceholderText:'{{__("Search")}}...',
        allOptionsSelectedText:'{{__("All")}}',
        search:true,
    });
    $('#phoneCode').on('change', function (e) {
        @this.set('phoneCode', $(this).val());
    });

</script>
		
	
</div>	