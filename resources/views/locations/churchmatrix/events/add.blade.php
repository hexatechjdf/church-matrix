<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg glass-effect">

            <div class="modal-header border-0 py-4 px-4 modal-gradient">
                <h4 class="modal-title fw-bold d-flex align-items-center" id="modalTitle">
                    <i class="fas fa-calendar-plus me-3"></i>
                    Add New Event
                </h4>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal"></button>
            </div>

            <form id="eventForm" method="POST">
                @csrf
                @method('POST')

                <div class="modal-body px-5 py-4">

                    <div class="text-center mb-4 animate-zoom">
                      
                    </div>

                    <div class="form-group mb-4">
                        <label class="fw-bold text-dark">Event Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="eventName"
                               class="form-control form-control-lg rounded-pill shadow-sm bg-light border-0"
                               placeholder="Enter Event"
                               required>
                    </div>

                </div>
                <div class="modal-footer border-0 pb-4 px-5">
                    <button type="button" class="btn btn-secondary rounded-pill px-4 shadow-sm" data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-lg btn-animated">
                        <i class="fas fa-save me-2"></i>
                        <span id="saveBtnText">Save Event</span>
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>