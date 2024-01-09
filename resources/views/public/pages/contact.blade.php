@extends('public.layout.template')

@section('content')
    <section id="contact-form">
        <div class="page-title background-blue">
            <h1 class="text-center sol-blue">Contact Us</h1>
        </div>
        <div class="container">
            <div class="contact-box">
                <div class="row background-yellow">
                    <div class="col-md-6">
                        <h4 class="text-center sol-blue" style="margin: 30px 0 20px;"><b>Send Us Message</b></h4>
                       
                        <form action="{{url('send-message')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="url" value="{{Request::url()}}" required>
                        <input type="hidden" name="subject" value="Contact" required>
                        <input type="hidden" name="additional_info" value="">
                        <div class="form-group row">
                                <div class="col-12 col-md-12">
                                    <input type="text" name="name" class="form-control" placeholder="Name" value="" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <input type="text" name="email" class="form-control" placeholder="Email" value="" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <input type="text" name="phone" class="form-control" placeholder="Phone" value="" required>
                                </div>
                                <div class="col-12 col-md-12">
                                    <textarea name="message" id="" cols="30" rows="5" class="form-control" style="resize: none;"></textarea>
                                </div>
                            </div>
                            <div class="form-group text-center">
                                <button type="submit" class="btn button-blue">Send Message</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-6" style="padding: 0;">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d7921.706401784648!2d107.608907!3d-6.908151!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x22ec707978824d85!2sSAKURA%20KOMPUTER!5e0!3m2!1sen!2sid!4v1614735368270!5m2!1sen!2sid" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        #contact-form input{
            margin-bottom: 15px;
        }
    </style>
@stop
