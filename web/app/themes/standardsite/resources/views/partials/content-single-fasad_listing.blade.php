@if(class_exists('PrekWeb\\Includes\\Fasad') && PrekWeb\Includes\Fasad::imageRoute())
  <?php echo('<pre>'.print_r('image route', true).'</pre>'); ?>
@else
  @include('fasad.content-single-fasad_listing')
@endif