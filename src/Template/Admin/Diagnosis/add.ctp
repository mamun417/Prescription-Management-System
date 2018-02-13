
<?= $this->Form->create($diagnosi) ?>
<section class="workspace">
    <div class="workspace-body">
        <div class="page-heading">
            <ol class="breadcrumb breadcrumb-small">
                <li><a href="<?=$this->Url->build(array('action' => 'index' )) ?>" title="<?= __('Diagnosi') ?>"> <?= __('Diagnosi') ?></a></li>
                <li class="active"><a href="#">Add <?= __('Diagnosi') ?></a></li>
            </ol>
        </div>
        <div class="main-container">
            <div class="content">
                <div class="page-wrap">
                    <div class="col-sm-12 col-md-12">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default panel-hovered panel-stacked">
                                    <div class="panel-heading"><?= __('Add Diagnosi') ?></div>
                                    <div class="panel-body">
                                        <?php
                                         echo'<div class="col-sm-6">
                                                    <div class="form-row">
                                                        <label class="name">Name<span class="required" aria-required="true"></span></label>
                                                        <div class="inputs">';
                                                            echo $this->Form->input('name', ['class' => 'form-control', 'label' => false, 'required' => true, 'type' =>'text']);
                                                        echo '</div>
                                                    </div>
                                                </div>';
                                                 echo'<div class="col-sm-6">
                                                    <div class="form-row">
                                                        <label class="name">Instructions<span class="required" aria-required="true"></span></label>
                                                        <div class="inputs">';
                                                            echo $this->Form->input('instructions', ['class' => 'form-control', 'label' => false, 'required' => true, 'type' =>'text']);
                                                        echo '</div>
                                                    </div>
                                                </div>';
                                                 echo'<div class="col-sm-6">
                                                    <div class="form-row">
                                                        <label class="name">Status<span class="required" aria-required="true"></span></label>
                                                        <div class="inputs">';
                                                            echo $this->Form->input('status', ['class' => 'form-control', 'label' => false, 'required' => true, 'type' =>'text']);
                                                        echo '</div>
                                                    </div>
                                                </div>';
                                                                                                echo $this->Form->input('medicines._ids', ['options' => $medicines]);
                                                    echo $this->Form->input('tests._ids', ['options' => $tests]);
     ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <footer class="footer ">
        <div class="flex-container">
            <a href="<?php echo $this->Url->build(array('action' => 'index' )) ?>" class="btn btn-default  btn-cancel" title="Cancel">Cancel</a>
            <div class="flex-item">
                <?= $this->Form->button(__('Submit'), ['class' => 'btn save event-save']) ?>
            </div>
        </div>
    </footer>
    
</section>
<?= $this->Form->end() ?>