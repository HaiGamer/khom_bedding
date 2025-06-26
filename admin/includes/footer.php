</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script>
// Hàm để kích hoạt CKEditor
function initializeCKEditor(elementId) {
   const editorElement = document.getElementById(elementId);
   if (editorElement) {
      ClassicEditor
         .create(editorElement)
         .catch(error => {
            console.error('There was a problem initializing the editor:', error);
         });
   }
}

// Kích hoạt cho trình soạn thảo Blog
initializeCKEditor('content-editor');

// Kích hoạt cho trình soạn thảo Mô tả Sản phẩm
initializeCKEditor('description-editor');
</script>


</body>

</html>