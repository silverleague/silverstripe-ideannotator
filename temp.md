#Contributing

Yes, please! But please adhere to the following rules, to keep everything neat and tidy.

#Branching and forking

When you want to contribute, please first create a fork of the master release first. At the time of writing, this is version `1.0.2`

On your own branch, you are free to work as you wish of course, but preferably, follow the rules of GitFlow. With one exception. If you plan to create a new feature you want to make into a pull request, don't create a feature branch, but create a pulls branch.
e.g. if you want to implement your awesome feature which can be summarised as "My Awesome Annotation", create a branch named "pulls/my-awesome-annotation".
If you want to fix an issue mentioned in issues, create a branch named "pulls/issue-{#}-description-of-issue". Where {#} should be replaced with the issue number ofcourse.

In case of the latter, please tag your commit with the issue number, by simply starting with `issue #15`, followed by a description of the issue that's fixed. This will tell github, your commit is linked to that specific issue.

Please try to keep everything in one commit. If needed, squash and force-push.

Rules above do not apply for collaborators/owner, who work on feature branches and/or hotfixes directly. (We have awesomeness powers)

#Code style

Please adhere to the [SilverStripe CodeOfConduct](CodeOfConduct.md).
