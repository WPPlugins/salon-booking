=== Salon Booking ===
Contributors: tanaka-hisao
Tags: hair salon,salon,appointment,booking,beauty apps,reservation,dental clinic,hospital,mutilingual,clinic,spa,散髪予約,美容院予約,美容室予約,サロン予約,エステ予約,予約システム,予約管理
Requires at least: 4.0
Tested up to: 4.8
Stable tag: 1.7.26
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Salon Booking enables the reservation to one-on-one business.

== Description ==

Salon Booking enables the reservation to one-on-one business
between a client and a staff member,
namely those businesses like hair salon, hospital, dental clinic and so on..

Salon Booking requires neither member registration to make reservations
from on the Web sites, nor loses prospective clients
who hesitate to register personal information.

To prevent the wrongful registration and reservation,
the reservation procedure is devised for the clients
with no registration at all as follows;
"tentative reservation",
"response to the e-mail address who tentatively reserved",
and "confirmation by the client on the confirmation screen on the Web sites".

And if a client agrees to register,
the reservation is done at once and
the change of the reservation is also becoming easier,
which might be an additional incentive for the clients to register.

The interface for the reservation is easy and like that of Google Calendar.
The change of the reservation is possible by means of drag and drop,
which enables also the staff member phoned by a client for the change
of the request can easily change the schedule accordingly.

Salon Booking is also capable of the personnel management of the staff member
on the shift control and time recording.
Of course the possible time ranges of reservation
and the attendance of the staff member co-relates automatically.

Salon Booking can also record the actual performance against the reservation
and capable of compiling the information on the demands from the clients
and working results.
So, it is very useful in improving the service quality and the operation management of staff.

== Installation ==
= New installation =

1. Upload "salon-booking" to the "/wp-content/plugins/" directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.

= Upgrade the plugin through the "Plugins" menu in WordPress =

1. Click "update now" of the plugin.
1. Deactivate the plugin.
1. Activate the plugin.

== Frequently Asked Questions ==
1. [Docs](http://salon.mallory.jp/en/?page_id=80)
1. [demo](https://salon.mallory.jp/en/?page_id=8)
1. [ドキュメント](http://salon.mallory.jp/?page_id=143)
1. [デモ](https://salon.mallory.jp/?page_id=16)

== Screenshots ==

1. Reservation Screen(PC）
1. Reservation Screen(Mobile）
1. Environment Setting
1. Setting of Staff
1. Time Card

== Changelog ==
= 1.7.26 =
* Fixed internal server error in customer and staff(PHP7)


= 1.7.25 =
* Fixed Shops holiday setting

= 1.7.24 =
* Changed Staff can not opereate other shops except admin.("Menu" and "Staff")

= 1.7.23 =
* Fixed Staff can not opereate other shops except admin in front.

= 1.7.22 =
* Changed Staff can not opereate other shops except admin. "Menu" and "Staff" will release next version.

= 1.7.21 =
* Fixed radio menu check

= 1.7.20 =
* Fixed escaped charctor in any screens.
* Added menu field can use radio button.

= 1.7.19 =
* Fixed check input end time

= 1.7.18 =
* Added embedded text({X-TO_MAIL},{X-TO_TEL}) in "Notification mail to staff"
* Added filter [salon_replace_mail_to_info]
* Changed all field id in reservation screen
* Fixed check logged in customer
* Fixed exception error message
* Fixed cancel reservation in mobile screen
* Fixed other bug

= 1.7.17 =
* Fixed check blur
* Fixed change defaul shops in shortcode

= 1.7.16 =
* Added default setting about login customer
* Changed color of disbled menu

= 1.7.15 =
* Added options about setting("on business")
* Added check of the donwload
* Fixed change style for "Twenty Seventeen"
* Fixed setting tag in "Reservation Screen"

= 1.7.14 =
* Added filter [salon_booking_set_init_display_for_mobile]

= 1.7.13 =
* Chenge font-family of this plugin
* Added filter [salon_booking_only_pc_remark]

= 1.7.12 =
* Added option to hide "today"'s button
* Chenge nemeless function to not nameles function.
* Added short code
* Fixed "LIGHTBOX" do not show
