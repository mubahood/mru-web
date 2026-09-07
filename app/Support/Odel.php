<?php

namespace App\Support;

/**
 * The university's Open, Distance and E-Learning system, as its own governing
 * documents describe it.
 *
 * Every fact here is traceable to one of two documents approved by the
 * University Council in December 2019 and commenced in January 2020:
 *
 *   DLP — Distance Learning Policy (11pp)
 *   FLP — Flexible Learning Policy (9pp)
 *
 * Both are scanned images rather than text, so nothing in them can be
 * searched, quoted or read aloud by a screen reader. They were transcribed by
 * OCR for this section, and every entry carries the clause it came from so a
 * reader can check it against the PDF.
 *
 * The `status` on each entry is the point of the whole section. The policies
 * say "shall" as often as they say "is": the Centre for Flexible and Distance
 * Learning "will be established", the Academic Success Coach and the toolkits
 * are commitments. Rendering all of that as though it were already running
 * would mislead an applicant into paying fees for a service nobody has
 * confirmed is staffed. So a claim is either:
 *
 *   Odel::IN_PLACE  — demonstrable today
 *   Odel::COMMITTED — approved by Council, not asserted as operating
 */
class Odel
{
    public const IN_PLACE = 'in_place';

    public const COMMITTED = 'committed';

    /** Council approval, commencement, and the review both documents set for themselves. */
    public const APPROVED = 'December 2019';

    public const COMMENCED = 'January 2020';

    public const REVIEW_YEARS = 5;

    /**
     * The six flexible study modes — FLP §8.
     *
     * The spine of this section. A policy buries them on page seven; here they
     * are the navigation, because choosing between them is the reader's actual
     * decision.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function modes(): array
    {
        return [
            'part-time' => [
                'name' => 'Part-Time',
                'clause' => 'FLP §8.1',
                'status' => self::IN_PLACE,
                'icon' => 'fa-clock',
                'suits' => 'You are already working, or carrying commitments that take most of your time.',
                'summary' => 'One or two course units a semester, at a pace set by what the rest of your life allows.',
                'detail' => [
                    'You may be able to take one or two course units in each semester.',
                    'The qualification takes longer to complete, but the pace is suited to your needs and demands.',
                    'A full-time student who cannot commit enough time may change to part-time study.',
                ],
                'watch' => 'A full-time student changing to part-time must withdraw before a set date to avoid an academic or financial penalty.',
            ],
            'distance-learning' => [
                'name' => 'Distance Learning',
                'clause' => 'FLP §8.2, DLP §1.1',
                'status' => self::IN_PLACE,
                'icon' => 'fa-tower-broadcast',
                'suits' => 'You cannot be on campus for regular classes — because of where you live, or what you do.',
                'summary' => 'Also called external study or open learning. Teaching reaches you instead of you reaching it.',
                'detail' => [
                    'You do not have to be on campus in order to study.',
                    'A number of courses are offered online, with lecture notes and learning materials available for download.',
                    'Teacher and student — or supervisor and research student — are physically in separate locations.',
                    'Some or all of the instructional content is delivered by an alternative method, in addition to or in place of face-to-face teaching.',
                ],
            ],
            'intensive' => [
                'name' => 'Intensive Mode',
                'clause' => 'FLP §8.3',
                'status' => self::IN_PLACE,
                'icon' => 'fa-bolt',
                'suits' => 'You can clear a block of time, but not a whole semester of weekly classes.',
                'summary' => 'A unit taught over five or six weeks, several consecutive weekends, or intensively across a single week.',
                'detail' => [
                    'Units are taught over shorter teaching periods.',
                    'Studying in intensive mode lets you complete a unit in much less time.',
                ],
                'watch' => 'Because intensive units have comparatively fewer classes, attendance is compulsory.',
            ],
            'project-based' => [
                'name' => 'Project-Based Study',
                'clause' => 'FLP §8.4',
                'status' => self::IN_PLACE,
                'icon' => 'fa-diagram-project',
                'suits' => 'You are working towards a postgraduate degree and learn best by doing the work itself.',
                'summary' => 'A research project, or a series of smaller ones, in place of taught contact.',
                'detail' => [
                    'Some courses involve a research project, or a series of smaller projects.',
                    'Normally part of a postgraduate degree.',
                    'Project-based units let you study at a pace more suited to your individual needs.',
                ],
            ],
            'individual-unit' => [
                'name' => 'Individual Course Unit',
                'clause' => 'FLP §8.5',
                'status' => self::IN_PLACE,
                'icon' => 'fa-cube',
                'suits' => 'You want one subject — for your job, for yourself, or to test the water before committing.',
                'summary' => 'A single course unit, without enrolling on a full programme.',
                'detail' => [
                    'You may be able to study an individual course unit as a visiting or cross-institutional student.',
                    'Study can be for professional or personal development.',
                    'It may count towards a degree at another institution.',
                    'It is also a way to see whether the study suits you before committing to a whole degree.',
                ],
            ],
            'through-employment' => [
                'name' => 'Learning through Employment',
                'clause' => 'FLP §8.6',
                'status' => self::COMMITTED,
                'icon' => 'fa-briefcase',
                'suits' => 'You are in work, and the work itself is where your learning already happens.',
                'summary' => 'A qualification built around your job, agreed between you, the University and your employer.',
                'detail' => [
                    'Most of the learning takes place through active and reflective engagement with work activities, underpinned by academic knowledge and skills.',
                    'Course units and programmes can be tailored to any subject and are negotiated between the learner, the University and the employer.',
                    'The three parties agree a unit or programme that builds on your work activities while satisfying the requirements for a qualification at the appropriate level.',
                    'The outcome is recorded in a learning contract which, once approved, is a formal academic document.',
                    'The contract may include a claim for credit for previous learning relevant to the course.',
                    'Courses typically involve work-based projects, a research project, and a module reflecting on learning in the workplace.',
                ],
                'watch' => 'The policy assigns the supporting toolkits, outline modules and Curriculum Design Guide to the Centre for Flexible and Distance Learning, which it establishes rather than describes as running.',
            ],
        ];
    }

    /**
     * What flexible learning is meant to let a learner do — FLP §4.
     *
     * @return list<array<string, string>>
     */
    public static function promises(): array
    {
        return [
            ['title' => 'Earn credit for what you already know', 'clause' => 'FLP §4(a)', 'status' => self::COMMITTED,
                'body' => 'You may draw on existing knowledge to complete assessments and make progress. Where it was gained — earlier courses, work experience, training — does not matter. "If the learner knows it and can show it, he/she can use it to earn credit."'],
            ['title' => 'Advance at your own pace', 'clause' => 'FLP §4(b)', 'status' => self::COMMITTED,
                'body' => 'Progress is measured against assessments of key competencies. You take an assessment when you are ready: pass one level and move to the next.'],
            ['title' => 'Personalised support', 'clause' => 'FLP §4(c)', 'status' => self::COMMITTED,
                'body' => 'A dedicated Academic Success Coach works with you to build a learning plan and a timeline fitted to your goals and knowledge, and points you to the resources you need.'],
            ['title' => 'Start when you want', 'clause' => 'FLP §4(d)', 'status' => self::COMMITTED,
                'body' => 'Starting and progress are not bound to a traditional semester schedule. You can begin at any time of year, and take breaks between subscription periods.'],
            ['title' => 'Skills that improve employability', 'clause' => 'FLP §4(e)', 'status' => self::COMMITTED,
                'body' => 'Passing assessments of critical competencies is how you prove mastery of the skills that matter for employment and for creating your own work.'],
        ];
    }

    /**
     * What a distance student is entitled to — DLP §7.1.
     *
     * Written as entitlements rather than as policy prose, because that is what
     * they are: the things the University commits to give a student who is not
     * in the room.
     *
     * @return list<array<string, string>>
     */
    public static function entitlements(): array
    {
        return [
            ['clause' => 'DLP §7.1.1(i)', 'text' => 'Information setting out what the Faculty is responsible for and what the University is responsible for in delivering your programme.'],
            ['clause' => 'DLP §7.1.1(ii)', 'text' => 'Module descriptors showing the intended learning outcomes and the teaching, learning and assessment methods.'],
            ['clause' => 'DLP §7.1.1(iii)', 'text' => 'A clear schedule for when your study materials arrive and when your work is assessed.'],
            ['clause' => 'DLP §7.1.1(iv)', 'text' => 'Study materials that meet the University\'s expectations for teaching quality, however they reach you.'],
            ['clause' => 'DLP §7.1.1(v)', 'text' => 'Provision that is subject to monitoring and review within a specified period.'],
            ['clause' => 'DLP §7.1.2(i)', 'text' => 'A schedule of the learner support available to you — timetabled tutorials or web-based services.'],
            ['clause' => 'DLP §7.1.2(ii)', 'text' => 'Clear, current information about the support available to you locally and remotely.'],
            ['clause' => 'DLP §7.1.2(iii)', 'text' => 'Documents setting out your own responsibilities as a learner, and the University\'s commitments to you.'],
            ['clause' => 'DLP §7.1.3(i)', 'text' => 'A named contact — local or remote — who gives you constructive feedback on your performance and authoritative guidance on your progression.'],
            ['clause' => 'DLP §7.1.3(ii)', 'text' => 'Regular opportunities to talk with other learners about the programme.'],
            ['clause' => 'DLP §7.1.3(iii)', 'text' => 'Proper opportunities to give formal feedback on your experience of the programme.'],
            ['clause' => 'DLP §7.1.4', 'text' => 'Support staff who have the right skills and receive appropriate training and development.'],
        ];
    }

    /**
     * The eight tests a programme must pass before it may be taught this way
     * — DLP §5.0. The answer to "is a distance degree worth the same?"
     *
     * @return list<array<string, string>>
     */
    public static function approvalCriteria(): array
    {
        return [
            ['text' => 'It meets a demonstrable need, and does not damage the University\'s existing undergraduate or graduate offerings.'],
            ['text' => 'A complete programme is offered, so enrolled students can graduate in a timely fashion.'],
            ['text' => 'There are sufficient facilities, faculty and support staff willing and able to deliver it at the required quality.'],
            ['text' => 'The Faculty and Department can still meet their other commitments, or have been released from them.'],
            ['text' => 'It meets every relevant University degree programme requirement.'],
            ['text' => 'It is comparable in quality to the on-campus version.'],
            ['text' => 'The integrity of the student\'s work and the credibility of the degree and its credits are ensured.'],
            ['text' => 'It adheres to the NCHE\'s guidelines on distance and correspondence education.'],
        ];
    }

    /**
     * Who is answerable for what — DLP §6.4 and §7.0, FLP §7.0.
     *
     * @return list<array<string, mixed>>
     */
    public static function bodies(): array
    {
        return [
            ['name' => 'The Senate', 'clause' => 'DLP §6.4(i)', 'status' => self::IN_PLACE,
                'role' => 'Ensures sound and acceptable practice in deciding how much credit is awarded, and at what level. It approves the programmes the University offers.'],
            ['name' => 'The Faculties', 'clause' => 'DLP §6.4(ii)', 'status' => self::IN_PLACE,
                'role' => 'Carry primary responsibility for distance learning and oversee it through their committees — the rigour of the courses, the quality of instruction, and whether enough qualified members are there to develop, design, teach and oversee the programmes.'],
            ['name' => 'Department and Faculty Committees', 'clause' => 'DLP §6.4(iii)', 'status' => self::IN_PLACE,
                'role' => 'Ensure, with Senate approval, that programmes are coherent, compatible with the University\'s mission, and appropriate to offer in higher education.'],
            ['name' => 'Office of Distance Learning (ODL)', 'clause' => 'DLP §7.0', 'status' => self::COMMITTED,
                'role' => 'Runs the distance programmes and gives students access to them; works with the Academic Registrar\'s Office on a mandatory online orientation; gives academic departments the guidance and technical help to develop, deliver, assess and improve distance provision; and maintains the data and reporting on retention, enrolments and trends.'],
            ['name' => 'Centre for Flexible and Distance Learning', 'clause' => 'FLP §7.0(iv)', 'status' => self::COMMITTED,
                'role' => 'Coordinates all flexible learning developments, and produces the outline modules, the toolkits for learners, lecturers and employers, and the Curriculum Design Guide. The Flexible Learning Policy establishes this Centre rather than describing one already at work.'],
            ['name' => 'DL Assessment and Testing Unit', 'clause' => 'DLP §8.2', 'status' => self::COMMITTED,
                'role' => 'Sits within the Academic Registrar\'s Office. Provides secure testing environments, runs mentor, instructor and course evaluations for both distance and face-to-face courses, and supports instructors in designing secure assessment.'],
        ];
    }

    /** The two governing documents, for download. @return list<array<string,string>> */
    public static function policies(): array
    {
        return [
            ['title' => 'Distance Learning Policy', 'file' => 'documents/Distance-Learning-Policy.pdf', 'pages' => '11 pages'],
            ['title' => 'Flexible Learning Policy', 'file' => 'documents/Flexible-Learning-Policy.pdf', 'pages' => '9 pages'],
        ];
    }

    /** Human label for a status value. */
    public static function statusLabel(string $status): string
    {
        return $status === self::IN_PLACE ? 'In place' : 'Established by policy';
    }
}
