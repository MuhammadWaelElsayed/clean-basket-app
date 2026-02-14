<script>
  document.addEventListener('livewire:load', function () {
      Livewire.on('success', message => {
          Swal.fire({ text: message, icon: "success",
          buttonsStyling: !1, confirmButtonText: "{{__('Ok, got it')}}!",
          customClass: { confirmButton: "btn btn-base" } });
      });
  });
  document.addEventListener('livewire:load', function () {
      Livewire.on('error', message => {
          Swal.fire({ text: message, icon: "error",
          buttonsStyling: !1, confirmButtonText: "{{__('Ok, got it')}}!",
          customClass: { confirmButton: "btn btn-base" } });
      });
  });
  document.addEventListener('livewire:load', function () {
      Livewire.on('warning', message => {
          Swal.fire({ text: message, icon: "warning",
          buttonsStyling: !1, confirmButtonText: "{{__('Ok, got it')}}!",
          customClass: { confirmButton: "btn btn-warning" } });
      });
  });
</script>
