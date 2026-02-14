<script>
      Livewire.on('success', message => {
        console.log("here");
          Swal.fire({ text: message, icon: "success",
          buttonsStyling: !1, confirmButtonText: "Ok, got it!",
          customClass: { confirmButton: "btn btn-base" } });
      });
      Livewire.on('error', message => {
          Swal.fire({ text: message, icon: "error",
          buttonsStyling: !1, confirmButtonText: "Ok, got it!",
          customClass: { confirmButton: "btn btn-base" } });
      });
      Livewire.on('warning', message => {
          Swal.fire({ text: message, icon: "warning",
          buttonsStyling: !1, confirmButtonText: "Ok, got it!",
          customClass: { confirmButton: "btn btn-warning" } });
      });
</script>
